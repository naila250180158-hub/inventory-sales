@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
    <h1>Pesanan Saya</h1>

    <div class="table-wrap">
        <table>
            <tr><th>No.</th><th>Tanggal</th><th class="text-right">Item</th><th class="text-right">Total</th><th>Status</th></tr>
            @forelse ($orders as $order)
                <tr>
                    <td><a href="{{ route('orders.show', $order) }}">{{ $order->sale_number }}</a></td>
                    <td>{{ $order->sold_at->format('d/m/Y H:i') }}</td>
                    <td class="text-right">{{ $order->items_count }}</td>
                    <td class="text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    <td>
                        @if (! $order->isCompleted())
                            <span class="badge badge-cancel">Dibatalkan</span>
                        @elseif ($order->hasPendingCancelRequest())
                            <span class="badge badge-wait">Menunggu pembatalan</span>
                        @else
                            <span class="badge badge-ok">Selesai</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada pesanan. <a href="{{ route('shop.index') }}">Mulai belanja</a></td></tr>
            @endforelse
        </table>
    </div>

    {{ $orders->links('pagination.simple') }}
@endsection
