<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman tidak ditemukan</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="container">
    <h1>404 - Halaman tidak ditemukan</h1>
    <div class="card">
        <p>Halaman yang kamu cari tidak ada atau sudah dipindahkan.</p>
        <p><a class="btn" href="{{ url('/dashboard') }}">Kembali ke Beranda</a></p>
    </div>
</div>
</body>
</html>
