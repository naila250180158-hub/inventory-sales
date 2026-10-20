@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <h1>{{ $product->name }}</h1>

    <div class="card">
        <table>
            <tr><th>SKU</th><td>{{ $product->sku }}</td></tr>
            <tr><th>Kategori</th><td>{{ $product->category->name }}</td></tr>
            <tr><th>Harga beli</th><td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td></tr>
            <tr><th>Harga jual</th><td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td></tr>
            <tr>
                <th>Stok</th>
                <td>
                    {{ $product->stock }}
                    @if ($product->isLowStock()) <span class="low">(rendah)</span> @endif
                </td>
            </tr>
            <tr><th>Stok minimum</th><td>{{ $product->minimum_stock }}</td></tr>
            <tr><th>Dipakai di transaksi</th><td>{{ $product->purchase_items_count }} pembelian, {{ $product->sale_items_count }} penjualan</td></tr>
        </table>

        <a class="btn btn-light" href="{{ route('products.index') }}">Kembali</a>
        @can('manage-master')
            <a class="btn" href="{{ route('products.edit', $product) }}">Ubah</a>
            <form class="inline-form" method="POST" action="{{ route('products.destroy', $product) }}"
                  onsubmit="return confirm('Hapus produk ini?')">
                @csrf
                @method('DELETE')
                <button type="submit">Hapus</button>
            </form>
        @endcan
    </div>
@endsection
