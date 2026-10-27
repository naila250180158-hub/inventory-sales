<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Keranjang belanja customer. Isi keranjang disimpan di SESSION sebagai
 * [product_id => jumlah]. Harga TIDAK disimpan di keranjang: selalu dibaca dari database.
 */
class CartController extends Controller
{
    public function index(Request $request): View
    {
        $lines = $this->lines($request);
        $total = $lines->sum('subtotal');

        return view('cart.index', compact('lines', 'total'));
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $cart = $request->session()->get('cart', []);
        $newQty = ($cart[$product->id] ?? 0) + $data['quantity'];

        // pengecekan ramah di sisi keranjang; pengecekan final tetap di SaleService saat checkout
        if ($newQty > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya {$product->stock}.");
        }

        $cart[$product->id] = $newQty;
        $request->session()->put('cart', $cart);

        return back()->with('success', "{$product->name} masuk keranjang.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        if ($data['quantity'] > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya {$product->stock}.");
        }

        $cart = $request->session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id] = $data['quantity'];
            $request->session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Jumlah diperbarui.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang.');
    }

    // Checkout memakai SaleService yang sama dengan kasir: transaction, kunci stok, harga dari database.
    public function checkout(Request $request, SaleService $service): RedirectResponse
    {
        $items = $this->lines($request)
            ->map(fn ($line) => ['product_id' => $line['product']->id, 'quantity' => $line['quantity']])
            ->values()
            ->all();

        if ($items === []) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $sale = $service->create($request->user(), $items);

        // keranjang dikosongkan hanya kalau penjualan berhasil (kalau error, exception sudah keluar di atas)
        $request->session()->forget('cart');

        return redirect()->route('orders.show', $sale)->with('success', 'Pesanan berhasil dibuat.');
    }

    /**
     * Ubah isi session menjadi daftar baris lengkap (produk + jumlah + subtotal) untuk ditampilkan.
     *
     * @return Collection<int, array{product: Product, quantity: int, subtotal: float}>
     */
    private function lines(Request $request): Collection
    {
        $cart = $request->session()->get('cart', []);

        $products = Product::with('category')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            // produk yang sudah tidak ada otomatis dibuang dari tampilan
            ->filter(fn ($qty, $id) => $products->has($id))
            ->map(fn ($qty, $id) => [
                'product' => $products[$id],
                'quantity' => (int) $qty,
                'subtotal' => round((float) $products[$id]->selling_price * (int) $qty, 2),
            ]);
    }
}
