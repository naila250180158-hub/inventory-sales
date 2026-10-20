<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory & Sales') - Inventory Sales</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="{{ auth()->check() ? 'with-sidebar' : 'guest' }}">
@auth
    <aside class="sidebar">
        <div class="brand"><span class="brand-mark">◆</span> Inventory &amp; Sales</div>

        {{-- Menu admin disembunyikan untuk staff, tetapi yang menjaga tetap server (Gate di route). --}}
        <nav class="menu">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="ico">🏠</span> Beranda</a>
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><span class="ico">📦</span> Produk</a>
            <a href="{{ route('low-stock.index') }}" class="{{ request()->routeIs('low-stock.*') ? 'active' : '' }}"><span class="ico">⚠️</span> Stok Rendah</a>
            @can('manage-master')
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}"><span class="ico">🗂️</span> Kategori</a>
                <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><span class="ico">🚚</span> Pemasok</a>
            @endcan
            <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}"><span class="ico">📥</span> Pembelian</a>
            <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}"><span class="ico">🛒</span> Penjualan</a>
            @can('manage-users')
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="ico">👤</span> Pengguna</a>
            @endcan
        </nav>

        <div class="user-box">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->role }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout">Keluar</button>
            </form>
        </div>
    </aside>
@endauth

<main class="main">
    @auth
        <div class="greeting">Halo, <strong>{{ auth()->user()->name }}</strong> 👋</div>
    @endauth

    @if (session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif
    {{-- Error dari business rule yang tidak terkait satu kolom (mis. status transaksi) --}}
    @if ($errors->has('status'))
        <x-alert type="error">{{ $errors->first('status') }}</x-alert>
    @endif

    @yield('content')
</main>
</body>
</html>
