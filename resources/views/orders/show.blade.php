@extends('layouts.app')

@section('title', 'Detail Pesanan')

@section('content')
    <h1>Pesanan {{ $order->sale_number }}</h1>

    <div class="card">
        <p>Tanggal: {{ $order->sold_at->format('d/m/Y H:i') }}</p>
        <p>Status:
            @if (! $order->isCompleted())
                <span class="badge badge-cancel">Dibatalkan</span> ({{ $order->cancelled_at?->format('d/m/Y H:i') }})
            @elseif ($order->hasPendingCancelRequest())
                <span class="badge badge-wait">Menunggu keputusan pembatalan</span>
            @else
                <span class="badge badge-ok">Selesai</span>
            @endif
        </p>

        @if ($order->cancel_reason)
            <p>Alasan pembatalan kamu: <em>{{ $order->cancel_reason }}</em></p>
        @endif
        @if ($order->cancel_status === \App\Models\Sale::CANCEL_REJECTED)
            <p>Permintaan pembatalan <strong>ditolak</strong> owner.
                @if ($order->cancel_response) Alasan owner: <em>{{ $order->cancel_response }}</em> @endif
            </p>
        @endif
    </div>

    <div class="table-wrap">
        <table>
            <tr><th>Produk</th><th class="text-right">Jumlah</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="3" class="text-right">Total</th>
                <th class="text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</th>
            </tr>
        </table>
    </div>

    {{-- Form pengajuan pembatalan: hanya untuk pesanan selesai yang belum diajukan --}}
    @if ($order->isCompleted() && ! $order->hasPendingCancelRequest())
        <form class="card" method="POST" action="{{ route('orders.cancel-request', $order) }}">
            @csrf
            <h2>Ingin membatalkan pesanan ini?</h2>
            <p class="muted">Permintaan akan diteruskan ke owner. Stok baru dikembalikan kalau owner menyetujui.</p>
            <label for="reason">Alasan pembatalan</label>
            <input type="text" id="reason" name="reason" value="{{ old('reason') }}" maxlength="255" required style="width:100%">
            @error('reason') <small class="field-error">{{ $message }}</small> @enderror
            <p><button type="submit">Ajukan Pembatalan</button></p>
        </form>
    @endif

    <a href="{{ route('orders.index') }}">Kembali</a>
@endsection
