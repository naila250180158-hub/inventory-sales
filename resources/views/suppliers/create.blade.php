@extends('layouts.app')

@section('title', 'Tambah Pemasok')

@section('content')
    <h1>Tambah Pemasok</h1>
    <form class="card" method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        @include('suppliers._form')
        <p><button type="submit">Simpan</button> <a href="{{ route('suppliers.index') }}">Batal</a></p>
    </form>
@endsection
