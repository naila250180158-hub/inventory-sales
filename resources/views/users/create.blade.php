@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
    <h1>Tambah Pengguna</h1>
    <form class="card" method="POST" action="{{ route('users.store') }}">
        @csrf
        @include('users._form')
        <p><button type="submit">Simpan</button> <a href="{{ route('users.index') }}">Batal</a></p>
    </form>
@endsection
