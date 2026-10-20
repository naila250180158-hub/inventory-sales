@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
    <h1>Pembelian</h1>
    <p><a class="btn" href="{{ route('purchases.create') }}">+ Pembelian Baru</a></p>

    <form class="card filters" method="GET" action="{{ route('purchases.index') }}">
        <div>
            <label for="q">No. pembelian</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}">
        </div>
        <div>
            <label for="supplier">Pemasok</label>
            <select id="supplier" name="supplier">
                <option value="">Semua</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(request('supplier') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
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
            <a href="{{ route('purchases.index') }}">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <tr>
                <th>No.</th><th>Tanggal</th><th>Pemasok</th><th>Petugas</th>
                <th class="text-right">Item</th><th class="text-right">Total</th><th>Status</th>
            </tr>
            @forelse ($purchases as $purchase)
                <tr>
                    <td><a href="{{ route('purchases.show', $purchase) }}">{{ $purchase->purchase_number }}</a></td>
                    <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>{{ $purchase->user->name }}</td>
                    <td class="text-right">{{ $purchase->items_count }}</td>
                    <td class="text-right">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                    <td>
                        @if ($purchase->isCompleted())
                            <span class="badge badge-ok">Selesai</span>
                        @else
                            <span class="badge badge-cancel">Dibatalkan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Belum ada pembelian.</td></tr>
            @endforelse
        </table>
    </div>

    {{ $purchases->links('pagination.simple') }}
@endsection
