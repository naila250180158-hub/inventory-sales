<h2>Produk Terlaris</h2>
<div class="table-wrap">
    <table>
        <tr><th>#</th><th>Produk</th><th class="text-right">Terjual</th><th class="text-right">Pendapatan</th></tr>
        @forelse ($topProducts as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->product->name }}</td>
                <td class="text-right">{{ $row->qty }}</td>
                <td class="text-right">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="4">Belum ada penjualan.</td></tr>
        @endforelse
    </table>
</div>
