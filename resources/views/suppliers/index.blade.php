@extends('layouts.app')

@section('title', 'Pemasok')

@section('content')
    <h1>Pemasok</h1>
    <p><a class="btn" href="{{ route('suppliers.create') }}">+ Tambah Pemasok</a></p>

    <div class="table-wrap">
        <table>
            <tr><th>Nama</th><th>Telepon</th><th>Email</th><th>Jumlah Pembelian</th><th>Aksi</th></tr>
            @forelse ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td>{{ $supplier->email ?? '-' }}</td>
                    <td>{{ $supplier->purchases_count }}</td>
                    <td>
                        <a href="{{ route('suppliers.edit', $supplier) }}">Ubah</a>
                        <form class="inline-form" method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                              onsubmit="return confirm('Hapus pemasok ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada pemasok.</td></tr>
            @endforelse
        </table>
    </div>

    {{ $suppliers->links('pagination.simple') }}
@endsection
