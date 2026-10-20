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

        // Kelola kategori, produk, pemasok (tambah/ubah/hapus)
        Gate::define('manage-master', fn (User $user) => $user->isAdmin());

        // Kelola pengguna dan role
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());

        // Membuat pembelian dan penjualan: admin dan staff
        Gate::define('manage-transactions', fn (User $user) => in_array($user->role, ['admin', 'staff'], true));

        // Membatalkan transaksi: hanya admin
        Gate::define('cancel-transaction', fn (User $user) => $user->isAdmin());
    }
}
