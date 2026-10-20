@extends('layouts.app')

@section('title', 'Produk')

@section('content')
@php
    // Ikon emoji per kategori (hanya hiasan tampilan)
    $icons = [
        'Biji Kopi' => '☕', 'Peralatan Seduh' => '🫖', 'Minuman Siap Saji' => '🥤',
        'Susu & Bahan Tambahan' => '🥛', 'Camilan' => '🥐', 'Kemasan' => '📦',
    ];
@endphp
    <h1>Produk Toko</h1>

    @can('manage-master')
        <p><a class="btn" href="{{ route('products.create') }}">+ Tambah Produk</a></p>
    @endcan

    <form class="search-pill" method="GET" action="{{ route('products.index') }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 Cari SKU atau nama produk...">
        @if (request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <button type="submit">Cari</button>
        @if (request('q') || request('category'))
            <a class="btn btn-light" href="{{ route('products.index') }}">Reset</a>
        @endif
    </form>

    <div class="chips">
        <a class="chip {{ request('category') ? '' : 'active' }}" href="{{ route('products.index', array_filter(['q' => request('q')])) }}">Semua</a>
        @foreach ($categories as $category)
            <a class="chip {{ request('category') == $category->id ? 'active' : '' }}"
               href="{{ route('products.index', array_filter(['q' => request('q'), 'category' => $category->id])) }}">{{ $category->name }}</a>
        @endforeach
    </div>

    <div class="product-grid">
        @forelse ($products as $product)
            <div class="product-card">
                <div class="product-thumb tone-{{ $loop->index % 4 }}">{{ $icons[$product->category->name] ?? '📦' }}</div>
                <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
                <div class="product-sku">{{ $product->sku }} · {{ $product->category->name }}</div>
                <div class="product-bottom">
                    <span class="price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                    <span class="stock-pill {{ $product->isLowStock() ? 'low-pill' : '' }}">stok {{ $product->stock }}</span>
                </div>
                <div class="card-actions">
                    <a href="{{ route('products.show', $product) }}">Detail</a>
                    @can('manage-master')
                        <a href="{{ route('products.edit', $product) }}">Ubah</a>
                        <form class="inline-form" method="POST" action="{{ route('products.destroy', $product) }}"
                              onsubmit="return confirm('Hapus produk ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p class="muted">Produk tidak ditemukan.</p>
        @endforelse
    </div>

    {{ $products->links('pagination.simple') }}
@endsection
