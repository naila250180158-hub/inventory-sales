@extends('layouts.app')

@section('title', 'Pembelian Baru')

@section('content')
    <h1>Pembelian Baru</h1>
    <form class="card" method="POST" action="{{ route('purchases.store') }}">
        @csrf
        <label for="supplier_id">Pemasok</label>
        <select id="supplier_id" name="supplier_id" required>
            <option value="">-- pilih pemasok --</option>
            @foreach ($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        @error('supplier_id') <span class="field-error">{{ $message }}</span> @enderror

        <h2>Item</h2>
        @include('partials.items-form', ['withCost' => true, 'priceField' => 'purchase_price'])
        <button type="submit">Simpan Pembelian</button>
        <a href="{{ route('purchases.index') }}">Batal</a>
    </form>
@endsection
