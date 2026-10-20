@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
    <h1>Tambah Produk</h1>

    <form class="card" method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products._form')
        <p>
            <button type="submit">Simpan</button>
            <a href="{{ route('products.index') }}">Batal</a>
        </p>
    </form>
@endsection
