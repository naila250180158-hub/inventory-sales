<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Terjadi kesalahan</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="container">
    <h1>500 - Terjadi kesalahan</h1>
    <div class="card">
        <p>Ada masalah di server. Coba lagi beberapa saat. Data kamu tidak berubah setengah-setengah karena semua perubahan stok dibungkus transaction.</p>
        <p><a class="btn" href="{{ url('/dashboard') }}">Kembali ke Beranda</a></p>
    </div>
</div>
</body>
</html>
