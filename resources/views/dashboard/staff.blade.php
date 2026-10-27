@extends('layouts.app')

@section('title', 'Dashboard Staff')

@section('content')
    <h1>Dashboard Staff</h1>
    <p class="muted">Angka di bawah hanya transaksi milikmu.</p>

    <div class="cards">
        <div class="stat">
            <div class="num">Rp {{ number_format($stats['sales_today'], 0, ',', '.') }}</div>
            <div class="label">Penjualanmu hari ini</div>
        </div>
        <div class="stat">
            <div class="num">Rp {{ number_format($stats['sales_month'], 0, ',', '.') }}</div>
            <div class="label">Penjualanmu bulan ini</div>
        </div>
        <div class="stat">
            <div class="num">{{ $stats['sales_count'] + $stats['purchases_count'] }}</div>
            <div class="label">Transaksimu ({{ $stats['sales_count'] }} jual, {{ $stats['purchases_count'] }} beli)</div>
        </div>
        <div class="stat">
            <div class="num {{ $stats['low_stock_count'] > 0 ? 'low' : '' }}">{{ $stats['low_stock_count'] }}</div>
            <div class="label">Produk stok rendah</div>
        </div>
    </div>

    <p>
        <a class="btn" href="{{ route('sales.create') }}">+ Penjualan baru</a>
        <a class="btn btn-light" href="{{ route('purchases.create') }}">+ Pembelian baru</a>
    </p>

    <h2>Transaksi Terakhir Saya</h2>
    <div class="table-wrap">
        <table>
            <tr><th>No.</th><th>Tanggal</th><th class="text-right">Total</th><th>Status</th></tr>
            @forelse ($recentSales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show', $sale) }}">{{ $sale->sale_number }}</a></td>
                    <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                    <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    <td>
                        @if ($sale->isCompleted())
                            <span class="badge badge-ok">Selesai</span>
                        @else
                            <span class="badge badge-cancel">Dibatalkan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Kamu belum membuat penjualan.</td></tr>
            @endforelse
        </table>
    </div>

    <h2>Tren 7 Hari Terakhirmu</h2>
    <div class="card">
        @include('partials.trend-chart', ['trend' => $trend])
    </div>

    @include('partials.low-stock-table')
@endsection
