@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
    <h1>Penjualan {{ $sale->sale_number }}</h1>
    <div class="card">
        <p>Tanggal: {{ $sale->sold_at->format('d/m/Y H:i') }}</p>
        <p>Kasir: {{ $sale->user->name }}</p>
        <p>Status:
            @if ($sale->isCompleted())
                <span class="badge badge-ok">Selesai</span>
            @else
                <span class="badge badge-cancel">Dibatalkan</span>
                ({{ $sale->cancelled_at?->format('d/m/Y H:i') }})
            @endif
        </p>
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
                <button type="submit">Batalkan Penjualan</button>
            </form>
        @endif
    @endcan
    <a href="{{ route('sales.index') }}">Kembali</a>
@endsection
