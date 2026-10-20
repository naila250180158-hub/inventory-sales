<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $sales = Sale::with('user')
            ->withCount('items')
            // staff hanya melihat penjualannya sendiri
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('sale_number', 'like', "%{$v}%"))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('sold_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('sold_at', '<=', $v))
            ->latest('sold_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get(['id', 'sku', 'name', 'selling_price', 'stock']);

        return view('sales.create', compact('products'));
    }

    public function store(SaleRequest $request, SaleService $service): RedirectResponse
    {
        $sale = $service->create($request->user(), $request->validated()['items']);

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan berhasil disimpan.');
    }

    public function show(Request $request, Sale $sale): View
    {
        $this->authorizeView($request, $sale);

        $sale->load(['items.product', 'user']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale, SaleService $service): RedirectResponse
    {
        $service->cancel($sale);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Penjualan dibatalkan dan stok dikembalikan.');
    }

    // Staff hanya boleh melihat penjualannya sendiri, admin boleh semua
    private function authorizeView(Request $request, Sale $sale): void
    {
        abort_unless($request->user()->isAdmin() || $sale->user_id === $request->user()->id, 403);
    }
}
