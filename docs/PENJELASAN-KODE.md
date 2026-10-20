# Panduan Menjelaskan Kode (untuk demo/wawancara)

## Alur umum (MVC)
Browser → `routes/web.php` → Controller → (Form Request validasi) → Service/Model → View Blade.

## 1. Login & peran
- `AuthController`: cek email/password dengan `Auth::attempt`, `session()->regenerate()`.
- Peran ada di kolom `users.role`. `AppServiceProvider` mendefinisikan Gate (`manage-master`, `manage-users`, ...). Route dilindungi `->middleware('can:manage-master')`. Jadi staff yang mengetik URL admin tetap kena 403 (dijaga di server, bukan cuma disembunyikan menu).

## 2. Penjualan (inti UTS) — `SaleService::create`
1. `DB::transaction` → semua atau tidak sama sekali.
2. Item duplikat digabung, diurutkan berdasarkan product_id (cegah deadlock).
3. Tiap produk dikunci `lockForUpdate()` agar dua kasir tidak menjual stok terakhir bersamaan.
4. Jika stok < jumlah → `ValidationException` → rollback, stok tidak berubah.
5. Harga diambil dari database (`selling_price`), subtotal & total dihitung server. Input harga dari form tidak dipercaya.
6. `decrement('stock', qty)`.

## 3. Pembatalan
- Penjualan: stok dikembalikan (`increment`), status `cancelled`. Dua kali batal ditolak.
- Pembelian: ditolak bila stok sekarang < jumlah yang dibeli (stok sudah terjual).

## 4. Pembelian — `PurchaseService`
Sama polanya, tetapi stok bertambah dan ada `unit_cost`.

## 5. Hapus produk
`Product::hasTransactions()` → bila true, controller menolak hapus (riwayat transaksi terjaga). FK juga `restrictOnDelete`.

## 6. Manajemen pengguna
`UserController`: admin tidak bisa menghapus/mengubah peran dirinya, user bertransaksi tidak bisa dihapus. Password di-hash otomatis oleh cast `hashed`.

## 7. Validasi
`ProductRequest`, `SaleRequest`, `PurchaseRequest`, `UserRequest`. Pesan Indonesia di `lang/id/validation.php`.

## 8. Test
`tests/Feature/*` memakai `RefreshDatabase` + SQLite memori. Contoh: test overselling memastikan stok tidak berubah dan tidak ada sale yang tersimpan.

## Pertanyaan yang mungkin ditanya
- Kenapa pakai transaction? → konsistensi data.
- Kenapa lockForUpdate? → race condition.
- Kenapa total dihitung server? → mencegah manipulasi form.
- Bedanya Gate dan middleware auth? → auth = sudah login; can = punya izin.
- Apa itu N+1 dan cara mengatasinya? → eager loading `with()`, `withCount()`.
