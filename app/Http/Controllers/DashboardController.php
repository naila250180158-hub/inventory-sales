<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Admin melihat semua transaksi. Staff hanya melihat transaksinya sendiri.
        $sales = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));

        $purchases = Purchase::query()
            ->where('status', Purchase::STATUS_COMPLETED)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id));

        // Semua angka dihitung dari database memakai query agregat (COUNT / SUM)
        $stats = [
            'total_products' => Product::count(),
            'sales_today' => (clone $sales)->whereDate('sold_at', today())->sum('total'),
            'sales_month' => (clone $sales)
                ->whereYear('sold_at', now()->year)
                ->whereMonth('sold_at', now()->month)
                ->sum('total'),
            'sales_count' => (clone $sales)->count(),
            'purchases_count' => (clone $purchases)->count(),
            'low_stock_count' => Product::lowStock()->count(),
        ];

        $lowStock = Product::lowStock()->with('category')->orderBy('stock')->limit(10)->get();

        return view('dashboard', compact('stats', 'lowStock'));
    }
}
