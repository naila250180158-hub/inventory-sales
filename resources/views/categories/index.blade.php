@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <h1>Kategori</h1>
    <p><a class="btn" href="{{ route('categories.create') }}">+ Tambah Kategori</a></p>

    <div class="table-wrap">
        <table>
            <tr><th>Nama</th><th>Jumlah Produk</th><th>Aksi</th></tr>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category) }}">Ubah</a>
                        <form class="inline-form" method="POST" action="{{ route('categories.destroy', $category) }}"
                              onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada kategori.</td></tr>
            @endforelse
        </table>
    </div>

    {{ $categories->links('pagination.simple') }}
@endsection
