@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
    <h1>Penjualan {{ $sale->sale_number }}</h1>
    <div class="card">
        <p>Tanggal: {{ $sale->sold_at->format('d/m/Y H:i') }}</p>
        <p>{{ $sale->user->isCustomer() ? 'Pelanggan' : 'Kasir' }}: {{ $sale->user->name }}</p>
        <p>Status:
            @if ($sale->isCompleted())
                <span class="badge badge-ok">Selesai</span>
            @else
                <span class="badge badge-cancel">Dibatalkan</span>
                ({{ $sale->cancelled_at?->format('d/m/Y H:i') }})
            @endif
            @if ($sale->hasPendingCancelRequest())
                <span class="badge badge-wait">Menunggu keputusan pembatalan</span>
            @endif
        </p>
        @if ($sale->cancel_reason)
            <p>Alasan pembatalan dari pelanggan: <em>{{ $sale->cancel_reason }}</em></p>
        @endif
        @if ($sale->cancel_status === \App\Models\Sale::CANCEL_REJECTED)
            <p>Permintaan pembatalan sudah ditolak.
                @if ($sale->cancel_response) Alasan: <em>{{ $sale->cancel_response }}</em> @endif
            </p>
        @endif
    </div>

    <div class="table-wrap">
        <table>
            <tr><th>SKU</th><th>Produk</th><th class="text-right">Jumlah</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</th>
            </tr>
        </table>
    </div>

    @can('cancel-transaction')
        @if ($sale->isCompleted())
            <form class="inline-form" method="POST" action="{{ route('sales.cancel', $sale) }}"
                  onsubmit="return confirm('Batalkan penjualan ini? Stok akan dikembalikan.')">
                @csrf
                <button type="submit">{{ $sale->hasPendingCancelRequest() ? 'Setujui Pembatalan' : 'Batalkan Penjualan' }}</button>
            </form>

            {{-- Owner juga boleh menolak permintaan pembatalan dari customer --}}
            @if ($sale->hasPendingCancelRequest())
                <form class="card" method="POST" action="{{ route('sales.reject-cancel', $sale) }}">
                    @csrf
                    <label for="response">Alasan penolakan (opsional)</label>
                    <input type="text" id="response" name="response" maxlength="255" style="width:100%">
                    <p><button type="submit" class="btn-light">Tolak Permintaan</button></p>
                </form>
            @endif
        @endif
    @endcan
    <a href="{{ route('sales.index') }}">Kembali</a>
@endsection
