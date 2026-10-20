<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * @param  array<int, array{product_id:int|string, quantity:int|string, unit_cost:int|float|string}>  $items
     */
    public function create(User $user, int $supplierId, array $items): Purchase
    {
        // urutkan per produk supaya urutan penguncian selalu sama (mencegah deadlock)
        usort($items, fn ($a, $b) => (int) $a['product_id'] <=> (int) $b['product_id']);

        return DB::transaction(function () use ($user, $supplierId, $items) {
            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'user_id' => $user->id,
                'purchase_number' => 'TMP-' . Str::uuid(),
                'total' => 0,
                'status' => Purchase::STATUS_COMPLETED,
                'purchased_at' => now(),
            ]);

            $total = 0.0;

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $cost = (float) $item['unit_cost'];

                if ($qty < 1 || $cost < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Jumlah harus lebih dari 0 dan harga tidak boleh negatif.',
                    ]);
                }

                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $subtotal = round($cost * $qty, 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $subtotal,
                ]);

                // stok bertambah hanya karena transaksi ini berhasil tersimpan
                $product->increment('stock', $qty);
                $total = round($total + $subtotal, 2);
            }

            $purchase->update([
                'purchase_number' => 'PO-' . now()->format('Ymd') . '-' . str_pad((string) $purchase->id, 5, '0', STR_PAD_LEFT),
                'total' => $total,
            ]);

            return $purchase;
        });
    }

    /**
     * Pembatalan pembelian: stok dikurangi lagi. Ditolak kalau stok sekarang
     * sudah lebih kecil dari jumlah yang dibatalkan (barangnya sudah terjual),
     * supaya stok tidak pernah negatif.
     */
    public function cancel(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);

            if (! $purchase->isCompleted()) {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi ini sudah dibatalkan.',
                ]);
            }

            foreach ($purchase->items()->orderBy('product_id')->get() as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'status' => "Pembelian tidak bisa dibatalkan: stok {$product->name} tinggal {$product->stock}, "
                            . "kurang dari {$item->quantity} yang akan ditarik.",
                    ]);
                }

                $product->decrement('stock', $item->quantity);
            }

            $purchase->update([
                'status' => Purchase::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $purchase;
        });
    }
}
