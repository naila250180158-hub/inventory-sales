@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
    <h1>Pembelian {{ $purchase->purchase_number }}</h1>
    <div class="card">
        <p>Tanggal: {{ $purchase->purchased_at->format('d/m/Y H:i') }}</p>
        <p>Pemasok: {{ $purchase->supplier->name }}</p>
        <p>Petugas: {{ $purchase->user->name }}</p>
        <p>Status:
            @if ($purchase->isCompleted())
                <span class="badge badge-ok">Selesai</span>
            @else
                <span class="badge badge-cancel">Dibatalkan</span>
                ({{ $purchase->cancelled_at?->format('d/m/Y H:i') }})
            @endif
        </p>
    </div>

    <div class="table-wrap">
        <table>
            <tr><th>SKU</th><th>Produk</th><th class="text-right">Jumlah</th><th class="text-right">Harga beli</th><th class="text-right">Subtotal</th></tr>
            @foreach ($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($purchase->total, 0, ',', '.') }}</th>
            </tr>
        </table>
    </div>

    @can('cancel-transaction')
        @if ($purchase->isCompleted())
            <form class="inline-form" method="POST" action="{{ route('purchases.cancel', $purchase) }}"
                  onsubmit="return confirm('Batalkan pembelian ini? Stok akan ditarik kembali.')">
                @csrf
                <button type="submit">Batalkan Pembelian</button>
            </form>
        @endif
    @endcan
    <a href="{{ route('purchases.index') }}">Kembali</a>
@endsection
