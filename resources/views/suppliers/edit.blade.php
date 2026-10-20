@extends('layouts.app')

@section('title', 'Ubah Pemasok')

@section('content')
    <h1>Ubah Pemasok</h1>
    <form class="card" method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf
        @method('PUT')
        @include('suppliers._form')
        <p><button type="submit">Simpan</button> <a href="{{ route('suppliers.index') }}">Batal</a></p>
    </form>
@endsection
