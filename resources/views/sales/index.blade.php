@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    <h1>Penjualan</h1>
    <p><a class="btn" href="{{ route('sales.create') }}">+ Penjualan Baru</a></p>

    <form class="card filters" method="GET" action="{{ route('sales.index') }}">
        <div>
            <label for="q">No. penjualan</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}">
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua</option>
                <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
            </select>
        </div>
        <div>
            <label for="date_from">Dari</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div>
            <label for="date_to">Sampai</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
        </div>
        <div>
            <button type="submit">Terapkan</button>
            <a href="{{ route('sales.index') }}">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <tr>
                <th>No.</th><th>Tanggal</th><th>Kasir</th><th class="text-right">Item</th>
                <th class="text-right">Total</th><th>Status</th><th>Aksi</th>
            </tr>
            @forelse ($sales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->sale_number }}</a></td>
                    <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td class="text-right">{{ $sale->items_count }}</td>
                    <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    <td>
                        @if ($sale->isCompleted())
                            <span class="badge badge-ok">Selesai</span>
                        @else
                            <span class="badge badge-cancel">Dibatalkan</span>
                        @endif
                    </td>
                    <td><a href="{{ route('sales.show', $sale) }}">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7">Belum ada penjualan.</td></tr>
            @endforelse
        </table>
    </div>

    {{ $sales->links('pagination.simple') }}
@endsection
