@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
    <h1>Ubah Pengguna</h1>
    <form class="card" method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('users._form')
        <p><button type="submit">Simpan</button> <a href="{{ route('users.index') }}">Batal</a></p>
    </form>
@endsection
