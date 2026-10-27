@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
    <div class="login-wrap">
        <div class="login-hero">
            <div class="brand"><span class="brand-mark">◆</span> Inventory &amp; Sales</div>
            <p>Buat akun pembeli untuk melihat katalog dan memesan produk.</p>
        </div>

        <form class="card login-card" method="POST" action="{{ route('register.store') }}">
            @csrf
            <h1>Daftar akun</h1>
            <p class="muted">Akun yang kamu buat berperan sebagai pembeli (customer).</p>

            <label for="name">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
            @error('name') <small class="field-error">{{ $message }}</small> @enderror

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email') <small class="field-error">{{ $message }}</small> @enderror

            <label for="password">Password (minimal 8 karakter)</label>
            <input type="password" id="password" name="password" required>
            @error('password') <small class="field-error">{{ $message }}</small> @enderror

            <label for="password_confirmation">Ulangi password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>

            <p><button type="submit" class="btn-block">Daftar</button></p>
            <p class="muted center">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        </form>
    </div>
@endsection
