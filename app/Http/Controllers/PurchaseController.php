<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'supplier' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $purchases = Purchase::with(['supplier', 'user'])
            ->withCount('items')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where('purchase_number', 'like', "%{$v}%"))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['supplier'] ?? null, fn ($q, $v) => $q->where('supplier_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('purchased_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('purchased_at', '<=', $v))
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get(['id', 'sku', 'name', 'purchase_price', 'stock']);

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(PurchaseRequest $request, PurchaseService $service): RedirectResponse
    {
        $data = $request->validated();

        $purchase = $service->create($request->user(), (int) $data['supplier_id'], $data['items']);

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil disimpan, stok bertambah.');
    }

    public function show(Request $request, Purchase $purchase): View
    {
        abort_unless($request->user()->isAdmin() || $purchase->user_id === $request->user()->id, 403);

        $purchase->load(['items.product', 'supplier', 'user']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase, PurchaseService $service): RedirectResponse
    {
        $service->cancel($purchase);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian dibatalkan dan stok ditarik kembali.');
    }
}
