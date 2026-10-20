@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Dashboard</h1>
    @unless (auth()->user()->isAdmin())
        <p class="muted">Angka penjualan dan pembelian di bawah hanya transaksi milikmu.</p>
    @endunless

    <div class="cards">
        <div class="stat">
            <div class="num">{{ $stats['total_products'] }}</div>
            <div class="label">Total produk</div>
        </div>
        <div class="stat">
            <div class="num">Rp {{ number_format($stats['sales_today'], 0, ',', '.') }}</div>
            <div class="label">Penjualan hari ini</div>
        </div>
        <div class="stat">
            <div class="num">Rp {{ number_format($stats['sales_month'], 0, ',', '.') }}</div>
            <div class="label">Penjualan bulan ini</div>
        </div>
        <div class="stat">
            <div class="num">{{ $stats['sales_count'] + $stats['purchases_count'] }}</div>
            <div class="label">Jumlah transaksi ({{ $stats['sales_count'] }} jual, {{ $stats['purchases_count'] }} beli)</div>
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

    <h2>Produk Stok Rendah</h2>
    <div class="table-wrap">
        <table>
            <tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Minimum</th></tr>
            @forelse ($lowStock as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                    <td>{{ $product->category->name }}</td>
                    <td class="low">{{ $product->stock }}</td>
                    <td>{{ $product->minimum_stock }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada produk dengan stok rendah.</td></tr>
            @endforelse
        </table>
    </div>
@endsection
