<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // customer tidak punya dashboard back office: arahkan ke katalog
        if ($user->isCustomer()) {
            return redirect()->route('shop.index');
        }

        // Owner melihat semua transaksi. Staff hanya melihat transaksinya sendiri.
        $all = $user->seesAllTransactions();

        $sales = Sale::query()
            ->where('status', Sale::STATUS_COMPLETED)
            ->when(! $all, fn ($q) => $q->where('user_id', $user->id));

        $purchases = Purchase::query()
            ->where('status', Purchase::STATUS_COMPLETED)
            ->when(! $all, fn ($q) => $q->where('user_id', $user->id));

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

        // Permintaan pembatalan dari customer yang menunggu keputusan (hanya owner yang memutuskan)
        $pendingCancels = $user->isOwner()
            ? Sale::where('cancel_status', Sale::CANCEL_REQUESTED)->count()
            : 0;

        // Grafik 7 hari terakhir dan produk terlaris: dihitung dari database setiap halaman dibuka,
        // jadi otomatis berubah begitu ada transaksi baru atau pembatalan.
        $trend = $this->trend($sales, $purchases);

        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->when(! $all, fn ($q) => $q->where('sales.user_id', $user->id))
            ->selectRaw('sale_items.product_id, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as revenue')
            ->groupBy('sale_items.product_id')
            ->orderByDesc('qty')
            ->orderBy('sale_items.product_id')
            ->limit(5)
            ->with('product')
            ->get();

        // 5 transaksi penjualan terakhir milik pengguna ini (dipakai di dashboard staff)
        $recentSales = Sale::where('user_id', $user->id)
            ->latest('sold_at')
            ->latest('id')
            ->limit(5)
            ->get();

        // Setiap peran punya dashboard sendiri: owner (grafik + keputusan) dan staff (tugas harian)
        $view = match (true) {
            $user->isOwner() => 'dashboard.owner',
            default => 'dashboard.staff',
        };

        return view($view, compact('stats', 'lowStock', 'pendingCancels', 'trend', 'topProducts', 'recentSales'));
    }

    /**
     * Total penjualan dan pembelian (yang tidak dibatalkan) per hari, 7 hari terakhir termasuk hari ini.
     * Hari tanpa transaksi tetap diisi 0 supaya grafiknya selalu 7 titik.
     *
     * @return array{labels: array<int, string>, sales: array<int, float>, purchases: array<int, float>}
     */
    private function trend($sales, $purchases): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => today()->subDays($i));
        $from = $days->first()->toDateString();

        $salesByDay = (clone $sales)
            ->whereDate('sold_at', '>=', $from)
            ->selectRaw('DATE(sold_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $purchasesByDay = (clone $purchases)
            ->whereDate('purchased_at', '>=', $from)
            ->selectRaw('DATE(purchased_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return [
            'labels' => $days->map(fn ($d) => $d->format('d/m'))->all(),
            'sales' => $days->map(fn ($d) => (float) ($salesByDay[$d->toDateString()] ?? 0))->all(),
            'purchases' => $days->map(fn ($d) => (float) ($purchasesByDay[$d->toDateString()] ?? 0))->all(),
        ];
    }
}
