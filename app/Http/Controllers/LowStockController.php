<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class LowStockController extends Controller
{
    public function index(): View
    {
        $products = Product::lowStock()
            ->with('category')
            ->orderBy('stock')
            ->paginate(10);

        return view('low-stock.index', compact('products'));
    }
}
