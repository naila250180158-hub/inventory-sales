@extends('layouts.app')

@section('title', 'Penjualan Baru')

@section('content')
    <h1>Penjualan Baru</h1>
    <form class="card" method="POST" action="{{ route('sales.store') }}">
        @csrf
        @include('partials.items-form', ['withCost' => false, 'priceField' => 'selling_price'])
        <button type="submit">Simpan Penjualan</button>
        <a href="{{ route('sales.index') }}">Batal</a>
    </form>
@endsection
