@extends('layouts.app')

@section('title', 'Stok Rendah')

@section('content')
    <h1>Produk Stok Rendah</h1>
    <p class="muted">Produk yang stoknya sama dengan atau di bawah batas minimum.</p>

    <div class="table-wrap">
        <table>
            <tr><th>SKU</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Minimum</th></tr>
            @forelse ($products as $product)
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

    {{ $products->links('pagination.simple') }}
@endsection
