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
