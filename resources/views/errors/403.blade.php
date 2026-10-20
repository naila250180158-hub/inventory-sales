<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses ditolak</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="container">
    <h1>403 - Akses ditolak</h1>
    <div class="card">
        <p>Kamu tidak punya izin untuk membuka halaman atau melakukan aksi ini.</p>
        <p><a class="btn" href="{{ url('/dashboard') }}">Kembali ke Dashboard</a></p>
    </div>
</div>
</body>
</html>
