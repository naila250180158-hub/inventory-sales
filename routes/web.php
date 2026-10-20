<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LowStockController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Halaman untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

// Semua halaman di bawah ini wajib login (middleware auth)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Master data: hanya admin (can:manage-master) ----
    // Ditulis SEBELUM route products.show supaya /products/create tidak dianggap {product}
    Route::middleware('can:manage-master')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });

    // Admin dan staff boleh melihat produk
    Route::resource('products', ProductController::class)->only(['index', 'show']);
    Route::get('/low-stock', [LowStockController::class, 'index'])->name('low-stock.index');

    // ---- Transaksi: admin dan staff (can:manage-transactions) ----
    Route::middleware('can:manage-transactions')->group(function () {
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });

    // ---- Pembatalan transaksi: hanya admin ----
    Route::middleware('can:cancel-transaction')->group(function () {
        Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    });

    // ---- Kelola pengguna dan role: hanya admin ----
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });
});
