# Panduan Menjelaskan Kode (untuk demo/wawancara)

## Alur umum (MVC)
Browser → `routes/web.php` → Controller → (Form Request validasi) → Service/Model → View Blade.

## 1. Login & peran
- `AuthController`: cek email/password dengan `Auth::attempt`, `session()->regenerate()`.
- Peran ada di kolom `users.role`. `AppServiceProvider` mendefinisikan Gate (`manage-master`, `manage-users`, ...). Route dilindungi `->middleware('can:manage-master')`. Jadi staff yang mengetik URL khusus owner tetap kena 403 (dijaga di server, bukan cuma disembunyikan menu).

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
`UserController`: owner (satu-satunya yang boleh kelola pengguna) tidak bisa menghapus/mengubah peran dirinya, user bertransaksi tidak bisa dihapus. Password di-hash otomatis oleh cast `hashed`.

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

---

## Tambahan: fitur Customer (pembeli)

### Alurnya
1. Pembeli buka `/register` → `AuthController@register` membuat user dengan `role = 'customer'`. Role **tidak** diambil dari form, jadi tidak ada yang bisa mendaftar sebagai owner.
2. Setelah login, customer diarahkan ke `/shop` (katalog). Owner dan staff ke `/dashboard`, dan masing-masing mendapat tampilan dashboard sendiri.
3. `CartController` menyimpan keranjang di **session** sebagai `[product_id => jumlah]`. Harga tidak disimpan di keranjang, selalu dibaca dari database.
4. `CartController@checkout` memanggil **`SaleService::create`** yang sama dengan kasir. Jadi aturan stok (transaction, `lockForUpdate`, tidak boleh melebihi stok, harga dari database) otomatis berlaku untuk pembeli.
5. `OrderController` menampilkan "Pesanan Saya": query selalu `where('user_id', pengguna login)`, dan `authorizeOwner()` memberi 403 kalau mencoba membuka pesanan orang lain.
6. Pembatalan: customer mengisi alasan → `SaleService::requestCancel` (status pembatalan jadi `requested`, **stok belum berubah**). Owner lalu:
   - **Setujui** → `SaleService::cancel` (stok kembali, `cancel_status = approved`), atau
   - **Tolak** → `SaleService::rejectCancel` (pesanan tetap berlaku, `cancel_status = rejected`).

### Pembatasan akses (di `AppServiceProvider` dan `routes/web.php`)
- Gate `shop` = hanya customer → grup route `/shop`, `/cart`, `/orders`.
- Gate `access-backoffice` = owner dan staff → produk, stok rendah. Customer yang membuka URL itu langsung mendapat 403.
- `/purchases`, `/sales`, `/users`, dan lainnya sudah dijaga gate lama, jadi customer otomatis 403.
- Hanya owner yang boleh memutuskan pembatalan (`can:cancel-transaction`).

### Kolom baru di tabel `sales` (migration `2025_01_02_000001_...`)
`cancel_status` (requested/rejected/approved), `cancel_reason`, `cancel_requested_at`, `cancel_response`.

### Kemungkinan pertanyaan dosen
- **Kenapa checkout pakai SaleService, bukan kode baru?** Supaya aturan stok ada di satu tempat; kalau diduplikasi, gampang ada celah overselling.
- **Bagaimana mencegah customer melihat pesanan orang lain?** Query dibatasi `user_id` untuk daftar, dan `abort_unless($sale->user_id === $user->id, 403)` untuk detail/pembatalan.
- **Bagaimana mencegah mendaftar sebagai owner?** `role` ditulis server (`'customer'`), isian `role` dari browser diabaikan. Ada tesnya: `test_register_ignores_role_sent_by_browser`.
- **Kenapa stok tidak langsung kembali saat customer minta batal?** Karena pembatalan butuh persetujuan owner; stok kembali hanya di `SaleService::cancel`.
- **Kenapa harga tidak disimpan di keranjang?** Harga bisa berubah dan input browser tidak boleh dipercaya; total selalu dihitung server.

---

## Tambahan: tiga peran (owner, staff/admin, customer)

| Gate | Siapa yang lolos | Dipakai untuk |
|---|---|---|
| `manage-master` | owner | CRUD kategori, produk, pemasok |
| `manage-users` | owner | kelola pengguna dan role |
| `manage-transactions` | owner, staff | membuat pembelian dan penjualan |
| `cancel-transaction` | owner | membatalkan transaksi dan memutuskan permintaan customer |
| `access-backoffice` | owner, staff | produk, stok rendah, dashboard |
| `shop` | customer | katalog, keranjang, pesanan saya |

- **Dashboard per peran**: `DashboardController` memilih view lewat `match`: `dashboard.owner` (grafik, produk terlaris, notifikasi pembatalan) dan `dashboard.staff` (angka dan transaksi milik sendiri). Customer dialihkan ke katalog.
- `User::seesAllTransactions()` bernilai true hanya untuk owner; staff dibatasi `where('user_id', ...)`.
- **Pertanyaan dosen**: *Kenapa pembatalan hanya owner?* Karena membatalkan transaksi mengubah stok dan omzet, jadi itu keputusan pemilik. Staff cukup mencatat transaksi.
- **Pertanyaan dosen**: *Kenapa staff tidak boleh mengubah produk?* Sesuai slide UTS: staff tidak boleh mengelola data master sensitif.
