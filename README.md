# Inventory & Sales Management System (UTS Pemrograman Web 2)

Aplikasi Laravel 12 untuk mengelola master data, pembelian (stok bertambah), penjualan (stok berkurang), dan pengguna dengan peran admin/staff.

## 1. Project overview
Toko mengelola kategori, produk, dan pemasok; mencatat pembelian dan penjualan dengan banyak item per transaksi; stok berubah otomatis dan aman (database transaction, tanpa overselling). Admin juga mengelola pengguna.

## 2. Requirements
- PHP 8.2 atau lebih baru (ekstensi: mbstring, openssl, pdo_mysql, zip; pdo_sqlite untuk menjalankan test)
- Composer 2
- MySQL / MariaDB (contoh: XAMPP)
- Tidak memerlukan Node.js (tampilan memakai satu file CSS statis di `public/css/app.css`, jadi `npm install` dan `npm run build` tidak diperlukan)

## 3. Installation
```
git clone <repository-url>
cd inventory-sales
composer install
copy .env.example .env      (Linux/Mac: cp .env.example .env)
php artisan key:generate
```

## 4. Environment setup
1. Buat database kosong bernama `inventory_sales` (phpMyAdmin atau `CREATE DATABASE inventory_sales;`).
2. Buka `.env`, pastikan `DB_DATABASE=inventory_sales`, `DB_USERNAME` dan `DB_PASSWORD` sesuai MySQL kamu. File `.env` tidak boleh di-commit.

## 5. Migration & seeding
```
php artisan migrate:fresh --seed
php artisan serve
```
Buka http://127.0.0.1:8000. Seeder membuat 2 akun demo, 6 kategori, 3 pemasok, 24 produk (SKU unik), 3 pembelian, dan 2 penjualan contoh. Untuk menjalankan test: `php artisan test`.

## 6. Demo account
| Peran | Email | Password |
|---|---|---|
| Admin | admin@example.com | admin12345 |
| Staff | staff@example.com | staff12345 |

(Hanya untuk demo lokal. Bukan kredensial pribadi.)

## 7. Features
- Login/logout, peran **admin** dan **staff** (dijaga di server lewat Gate + middleware `can:`).
- CRUD Kategori, Produk, Pemasok (admin); staff hanya melihat produk.
- Pembelian: banyak item, stok naik, bisa dibatalkan (ditolak bila stok sudah terjual).
- Penjualan: banyak item, stok turun, tidak boleh melebihi stok, bisa dibatalkan (stok kembali).
- Total dan harga dihitung **server** dari database, bukan dari input form.
- Produk yang sudah dipakai transaksi tidak bisa dihapus.
- Dashboard ringkasan dan halaman stok rendah.
- Pencarian, filter, dan pagination di produk, pembelian, penjualan.
- Manajemen pengguna (admin): tambah/ubah/hapus; admin tidak bisa menghapus dirinya atau mengubah perannya sendiri; pengguna yang punya transaksi tidak bisa dihapus.
- Staff hanya melihat transaksi miliknya sendiri.

## 8. Known limitations
- Pembelian tidak mengubah harga beli produk secara otomatis.
- Tidak ada edit transaksi; koreksi dilakukan dengan membatalkan lalu membuat ulang.
- Tidak ada laporan ekspor (PDF/Excel).
- Tampilan memakai CSS sendiri (tanpa Vite/Tailwind).

## ERD
![ERD](docs/screenshots/11-erd.png)

## Relasi Eloquent
- Category hasMany Product; Product belongsTo Category
- Supplier hasMany Purchase; Purchase belongsTo Supplier
- User hasMany Sale / Purchase; Sale / Purchase belongsTo User
- Sale hasMany SaleItem; Purchase hasMany PurchaseItem
- SaleItem / PurchaseItem belongsTo Product

## Aturan bisnis & keamanan
- Semua perubahan stok ada di `app/Services/SaleService.php` dan `PurchaseService.php`, dibungkus `DB::transaction` dengan `lockForUpdate` (mencegah race condition). Jika satu item gagal, semuanya dibatalkan (rollback).
- Produk duplikat dalam satu transaksi digabung dan diurutkan sebelum dikunci (mencegah bypass cek stok dan deadlock).
- Validasi lewat Form Request; SKU unik (validasi + unique constraint DB).
- CSRF aktif, password di-hash, login dibatasi (throttle), `.env` tidak di-commit.

## Pengujian
`php artisan test` menjalankan test fitur: login, produk, transaksi (stok, overselling, pembatalan, rollback), otorisasi peran, pencarian.
Bukti hasil terakhir (30 passed, 93 assertions) ada di [docs/test-results.txt](docs/test-results.txt).

## Screenshot
| | |
|---|---|
| **Login** | **Dashboard admin** |
| ![Login](docs/screenshots/01-login.png) | ![Dashboard admin](docs/screenshots/02-dashboard-admin.png) |
| **Produk (kartu, pencarian, filter kategori)** | **Penjualan dua item, total dihitung server** |
| ![Produk](docs/screenshots/03-produk.png) | ![Penjualan](docs/screenshots/04-penjualan-multi-item.png) |
| **Penjualan ditolak: stok tidak cukup** | **Detail pembelian (stok bertambah)** |
| ![Stok tidak cukup](docs/screenshots/05-stok-tidak-cukup.png) | ![Pembelian](docs/screenshots/06-pembelian-detail.png) |
| **Daftar pembelian (filter, pagination)** | **Manajemen pengguna (admin)** |
| ![Daftar pembelian](docs/screenshots/07-pembelian-daftar.png) | ![Pengguna](docs/screenshots/08-pengguna.png) |
| **Dashboard staff (menu admin tidak tampil)** | **Staff membuka /users: 403** |
| ![Dashboard staff](docs/screenshots/09-staff-dashboard.png) | ![403](docs/screenshots/10-staff-403.png) |

Bukti test otomatis: `php artisan test` menghasilkan **30 passed (93 assertions)**.
