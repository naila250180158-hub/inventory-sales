<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Katalog belanja untuk customer. Hanya membaca data produk.
 */
class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
        ]);

        $products = Product::with('category')
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($filters['category'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('shop.index', compact('products', 'categories'));
    }
}
