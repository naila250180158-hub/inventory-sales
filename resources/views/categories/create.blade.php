@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <h1>Tambah Kategori</h1>
    <form class="card" method="POST" action="{{ route('categories.store') }}">
        @csrf
        @include('categories._form')
        <p><button type="submit">Simpan</button> <a href="{{ route('categories.index') }}">Batal</a></p>
    </form>
@endsection
