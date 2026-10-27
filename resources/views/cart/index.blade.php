@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
    <h1>Keranjang</h1>

    {{-- Error dari checkout (mis. stok tidak cukup saat checkout) --}}
    @error('items')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    @if ($lines->isEmpty())
        <div class="card">
            <p>Keranjang masih kosong.</p>
            <p><a class="btn" href="{{ route('shop.index') }}">Mulai belanja</a></p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <tr><th>Produk</th><th class="text-right">Harga</th><th class="text-right">Jumlah</th><th class="text-right">Subtotal</th><th>Aksi</th></tr>
                @foreach ($lines as $line)
                    <tr>
                        <td>{{ $line['product']->name }}<br><small class="muted">{{ $line['product']->sku }} · stok {{ $line['product']->stock }}</small></td>
                        <td class="text-right">Rp {{ number_format($line['product']->selling_price, 0, ',', '.') }}</td>
                        <td class="text-right">
                            <form class="inline-form add-form" method="POST" action="{{ route('cart.update', $line['product']) }}">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="{{ $line['product']->stock }}" class="qty-input" aria-label="Jumlah">
                                <button type="submit">Ubah</button>
                            </form>
                        </td>
                        <td class="text-right">Rp {{ number_format($line['subtotal'], 0, ',', '.') }}</td>
                        <td>
                            <form class="inline-form" method="POST" action="{{ route('cart.remove', $line['product']) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="3" class="text-right">Perkiraan total</th>
                    <th class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</th>
                    <th></th>
                </tr>
            </table>
        </div>

        <p class="muted">Harga dan total final dihitung ulang oleh server saat pesanan dibuat.</p>

        <form class="inline-form" method="POST" action="{{ route('cart.checkout') }}">
            @csrf
            <button type="submit">Buat Pesanan</button>
        </form>
        <a class="btn btn-light" href="{{ route('shop.index') }}">Lanjut belanja</a>
    @endif
@endsection
