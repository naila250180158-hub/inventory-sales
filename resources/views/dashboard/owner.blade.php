@extends('layouts.app')

@section('title', 'Dashboard Owner')

@section('content')
    <h1>Dashboard Owner</h1>
    <p class="muted">Ringkasan seluruh toko: semua transaksi, grafik, dan keputusan pembatalan.</p>

    @if ($pendingCancels > 0)
        <div class="notice">
            <strong>{{ $pendingCancels }} permintaan pembatalan menunggu keputusanmu.</strong>
            <a class="btn" href="{{ route('sales.index', ['cancel' => 'requested']) }}">Periksa sekarang</a>
        </div>
    @endif

    @include('partials.stat-cards')

    <h2>Tren 7 Hari Terakhir</h2>
    <div class="card">
        @include('partials.trend-chart', ['trend' => $trend])
    </div>

    @include('partials.top-products-table')
    @include('partials.low-stock-table')
@endsection
