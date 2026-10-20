@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="login-wrap">
        <div class="login-hero">
            <div class="brand"><span class="brand-mark">◆</span> Inventory &amp; Sales</div>
            <p>Kelola stok, pembelian, dan penjualan dalam satu tempat.</p>
        </div>

        <form class="card login-card" method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <h1>Selamat datang</h1>
            <p class="muted">Masuk untuk melanjutkan.</p>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <small class="field-error">{{ $message }}</small>
            @enderror

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            @error('password')
                <small class="field-error">{{ $message }}</small>
            @enderror

            <p><button type="submit" class="btn-block">Masuk</button></p>
        </form>
    </div>
@endsection
