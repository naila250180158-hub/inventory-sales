<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Logika bisnis penjualan. Controller hanya memanggil class ini,
 * jadi aturan stok ada di SATU tempat.
 */
class SaleService
{
    /**
     * @param  array<int, array{product_id:int|string, quantity:int|string}>  $items
     */
    public function create(User $user, array $items): Sale
    {
        $lines = $this->mergeLines($items);

        // Semua langkah di dalam satu transaction: kalau ada exception, SEMUA di-rollback.
        return DB::transaction(function () use ($user, $lines) {
            $sale = Sale::create([
                'user_id' => $user->id,
                // nomor sementara, diganti setelah id diketahui (anti bentrok)
                'sale_number' => 'TMP-' . Str::uuid(),
                'total' => 0,
                'status' => Sale::STATUS_COMPLETED,
                'sold_at' => now(),
            ]);

            $total = 0.0;

            foreach ($lines as $productId => $qty) {
                // lockForUpdate: kunci baris produk supaya dua penjualan bersamaan
                // tidak membaca stok yang sama (mencegah overselling)
                $product = Product::lockForUpdate()->findOrFail($productId);

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup. Tersedia: {$product->stock}, diminta: {$qty}.",
                    ]);
                }

                // Harga diambil dari database, BUKAN dari input browser
                $unitPrice = $product->selling_price;
                $subtotal = round((float) $unitPrice * $qty, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total = round($total + $subtotal, 2);
            }

            $sale->update([
                'sale_number' => 'SL-' . now()->format('Ymd') . '-' . str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
                'total' => $total,
            ]);

            return $sale;
        });
    }

    /**
     * Pembatalan: status jadi cancelled dan stok dikembalikan, dalam satu transaction.
     */
    public function cancel(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {
            // ambil ulang dengan kunci, supaya dua pembatalan bersamaan tidak menggandakan stok
            $sale = Sale::lockForUpdate()->findOrFail($sale->id);

            if (! $sale->isCompleted()) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah dibatalkan.',
                ]);
            }

            foreach ($sale->items()->orderBy('product_id')->get() as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);
            }

            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $sale;
        });
    }

    /**
     * Gabungkan baris produk yang sama dan urutkan berdasarkan id produk
     * (urutan tetap mencegah deadlock saat mengunci banyak produk).
     *
     * @return array<int, int> product_id => total quantity
     */
    private function mergeLines(array $items): array
    {
        $lines = [];
        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $lines[$id] = ($lines[$id] ?? 0) + (int) $item['quantity'];
        }
        ksort($lines);

        return $lines;
    }
}
