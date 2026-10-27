<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LowStockController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Halaman untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');

    // Pendaftaran mandiri untuk customer (role selalu customer)
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});

// Semua halaman di bawah ini wajib login (middleware auth)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Master data: hanya owner (can:manage-master) ----
    // Ditulis SEBELUM route products.show supaya /products/create tidak dianggap {product}
    Route::middleware('can:manage-master')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });

    // Owner dan staff boleh melihat produk (customer memakai katalog /shop)
    Route::middleware('can:access-backoffice')->group(function () {
        Route::resource('products', ProductController::class)->only(['index', 'show']);
        Route::get('/low-stock', [LowStockController::class, 'index'])->name('low-stock.index');
    });

    // ---- Area customer: katalog, keranjang, pesanan saya (can:shop) ----
    Route::middleware('can:shop')->group(function () {
        Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{sale}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{sale}/cancel-request', [OrderController::class, 'requestCancel'])->name('orders.cancel-request');
    });

    // ---- Transaksi: owner dan staff (can:manage-transactions) ----
    Route::middleware('can:manage-transactions')->group(function () {
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });

    // ---- Pembatalan transaksi: hanya owner ----
    Route::middleware('can:cancel-transaction')->group(function () {
        Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
        Route::post('/sales/{sale}/reject-cancel', [SaleController::class, 'rejectCancel'])->name('sales.reject-cancel');
    });

    // ---- Kelola pengguna dan role: hanya owner ----
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });
});
