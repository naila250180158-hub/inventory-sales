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
