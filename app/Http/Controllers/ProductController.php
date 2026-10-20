<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
        ]);

        $products = Product::with('category') // eager loading: hindari N+1 query
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // filter tetap terbawa saat pindah halaman

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->load('category')->loadCount(['purchaseItems', 'saleItems']);

        return view('products.show', compact('product'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $product = new Product(['stock' => 0, 'minimum_stock' => 5]);

        return view('products.create', compact('categories', 'product'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil diubah.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Produk yang sudah dipakai transaksi TIDAK boleh dihapus (larangan hapus)
        if ($product->hasTransactions()) {
            return redirect()->route('products.index')
                ->with('error', 'Produk sudah dipakai di transaksi, tidak bisa dihapus.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
