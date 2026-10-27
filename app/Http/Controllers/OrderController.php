<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Pesanan Saya": customer melihat pesanannya sendiri dan boleh mengajukan pembatalan.
 */
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Sale::withCount('items')
            ->where('user_id', $request->user()->id) // hanya pesanan milik sendiri
            ->latest('sold_at')
            ->latest('id')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Sale $sale): View
    {
        $this->authorizeOwner($request, $sale);

        $sale->load('items.product');

        return view('orders.show', ['order' => $sale]);
    }

    public function requestCancel(Request $request, Sale $sale, SaleService $service): RedirectResponse
    {
        $this->authorizeOwner($request, $sale);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $service->requestCancel($sale, $data['reason']);

        return redirect()->route('orders.show', $sale)
            ->with('success', 'Permintaan pembatalan dikirim. Menunggu keputusan owner.');
    }

    // Customer tidak boleh membuka atau mengubah pesanan orang lain (cek di server)
    private function authorizeOwner(Request $request, Sale $sale): void
    {
        abort_unless($sale->user_id === $request->user()->id, 403);
    }
}
