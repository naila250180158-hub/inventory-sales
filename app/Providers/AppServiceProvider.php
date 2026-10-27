<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Zona waktu aplikasi diambil dari .env (APP_TIMEZONE). Default Asia/Jakarta (WIB),
        // supaya jam transaksi sesuai jam lokal, bukan UTC.
        $timezone = env('APP_TIMEZONE', 'Asia/Jakarta');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    public function boot(): void
    {
        // Gate = aturan "siapa boleh melakukan apa". Dipakai di route lewat middleware can:nama
        // dan di Blade lewat @can. Pengecekan terjadi di SERVER, bukan hanya menyembunyikan tombol.

        // Kelola kategori, produk, pemasok (tambah/ubah/hapus): hanya owner
        Gate::define('manage-master', fn (User $user) => $user->isOwner());

        // Kelola pengguna dan role: hanya owner
        Gate::define('manage-users', fn (User $user) => $user->isOwner());

        // Membuat pembelian dan penjualan: owner dan staff
        Gate::define('manage-transactions', fn (User $user) => in_array($user->role, ['owner', 'staff'], true));

        // Membatalkan transaksi dan memutuskan permintaan pembatalan customer: hanya owner
        Gate::define('cancel-transaction', fn (User $user) => $user->isOwner());

        // Halaman "back office" (produk, stok rendah, dashboard): owner dan staff, bukan customer
        Gate::define('access-backoffice', fn (User $user) => in_array($user->role, ['owner', 'staff'], true));

        // Halaman belanja (katalog, keranjang, pesanan saya): hanya customer
        Gate::define('shop', fn (User $user) => $user->isCustomer());
    }
}
