@extends('layouts.app')

@section('title', 'Ubah Kategori')

@section('content')
    <h1>Ubah Kategori</h1>
    <form class="card" method="POST" action="{{ route('categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('categories._form')
        <p><button type="submit">Simpan</button> <a href="{{ route('categories.index') }}">Batal</a></p>
    </form>
@endsection
