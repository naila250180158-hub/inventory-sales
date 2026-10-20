@extends('layouts.app')

@section('title', 'Ubah Produk')

@section('content')
    <h1>Ubah Produk</h1>
    <p class="muted">Stok saat ini: {{ $product->stock }}. Stok berubah lewat transaksi pembelian dan penjualan.</p>

    <form class="card" method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')
        @include('products._form')
        <p>
            <button type="submit">Simpan</button>
            <a href="{{ route('products.show', $product) }}">Batal</a>
        </p>
    </form>
@endsection
