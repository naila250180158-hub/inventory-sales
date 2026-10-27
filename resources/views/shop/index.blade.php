@extends('layouts.app')

@section('title', 'Katalog')

@section('content')
@php
    // Ikon emoji per kategori (hanya hiasan tampilan)
    $icons = [
        'Biji Kopi' => '☕', 'Peralatan Seduh' => '🫖', 'Minuman Siap Saji' => '🥤',
        'Susu & Bahan Tambahan' => '🥛', 'Camilan' => '🥐', 'Kemasan' => '📦',
    ];
@endphp
    <h1>Katalog Belanja</h1>

    <form class="search-pill" method="GET" action="{{ route('shop.index') }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 Cari produk...">
        @if (request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <button type="submit">Cari</button>
        @if (request('q') || request('category'))
            <a class="btn btn-light" href="{{ route('shop.index') }}">Reset</a>
        @endif
    </form>

    <div class="chips">
        <a class="chip {{ request('category') ? '' : 'active' }}" href="{{ route('shop.index', array_filter(['q' => request('q')])) }}">Semua</a>
        @foreach ($categories as $category)
            <a class="chip {{ request('category') == $category->id ? 'active' : '' }}"
               href="{{ route('shop.index', array_filter(['q' => request('q'), 'category' => $category->id])) }}">{{ $category->name }}</a>
        @endforeach
    </div>

    <div class="product-grid">
        @forelse ($products as $product)
            <div class="product-card">
                <div class="product-thumb tone-{{ $loop->index % 4 }}">{{ $icons[$product->category->name] ?? '📦' }}</div>
                <h3>{{ $product->name }}</h3>
                <div class="product-sku">{{ $product->category->name }}</div>
                <div class="product-bottom">
                    <span class="price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                    @if ($product->stock > 0)
                        <span class="stock-pill {{ $product->isLowStock() ? 'low-pill' : '' }}">stok {{ $product->stock }}</span>
                    @else
                        <span class="stock-pill low-pill">habis</span>
                    @endif
                </div>
                <div class="card-actions">
                    @if ($product->stock > 0)
                        <form class="inline-form add-form" method="POST" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="qty-input" aria-label="Jumlah">
                            <button type="submit">+ Keranjang</button>
                        </form>
                    @else
                        <span class="muted">Stok habis</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="muted">Produk tidak ditemukan.</p>
        @endforelse
    </div>

    {{ $products->links('pagination.simple') }}
@endsection
