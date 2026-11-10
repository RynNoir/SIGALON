<?php

use App\Http\Controllers\Admin\AdminCabangController;
use App\Http\Controllers\Admin\CabangController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Cabang\StafController;
use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('home');

Route::get('/akses-ditolak', fn () => response()->view('errors.403', [], 403))->name('akses-ditolak');

Route::get('/masuk', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('/masuk', [CustomerAuthController::class, 'login'])->name('customer.login.store');
Route::post('/daftar', [CustomerAuthController::class, 'register'])->name('customer.register');
Route::post('/keluar', [CustomerAuthController::class, 'logout'])->name('customer.logout');

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// [US-08] Pelanggan: lengkapi nomor telepon akun Google/pelanggan
// [US-11] Pelanggan: pesan air galon secara online
Route::middleware(['auth:pelanggan'])->group(function () {
    Route::get('/pelanggan/lengkapi-telepon', [CustomerAuthController::class, 'showCompletePhoneForm'])->name('customer.phone.edit');
    Route::post('/pelanggan/lengkapi-telepon', [CustomerAuthController::class, 'updatePhone'])->name('customer.phone.update');

    // [US-11] Pemesanan online air galon
    Route::livewire('pelanggan/pesan', 'pages::pelanggan.pesan')->name('pelanggan.pesan');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('dashboard/cabang/{cabang}', 'pages::dashboard-cabang')->name('dashboard.cabang');

    // [US-06] Super Admin: kelola data cabang dan akun Admin Cabang
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('cabang', CabangController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::resource('admin-cabang', AdminCabangController::class)
            ->parameters(['admin-cabang' => 'user'])
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::post('admin-cabang/{user}/toggle-active', [AdminCabangController::class, 'toggleActive'])
            ->name('admin-cabang.toggle-active');

        Route::get('admin-cabang/{user}/reset-password', [AdminCabangController::class, 'showResetPassword'])
            ->name('admin-cabang.reset-password');

        Route::post('admin-cabang/{user}/reset-password', [AdminCabangController::class, 'resetPassword'])
            ->name('admin-cabang.reset-password.store');

        // [US-14] Super Admin: unggah/ganti gambar QR statis
        Route::livewire('qr-statis', 'pages::admin.qr-statis')->name('qr-statis');
    });

    // [US-07] Admin Cabang: kelola akun Kasir & Kurir
    Route::prefix('cabang')->name('cabang.')->group(function () {
        Route::resource('staf', StafController::class)
            ->parameters(['staf' => 'user'])
            ->only(['index', 'create', 'store', 'edit', 'update']);

        Route::post('staf/{user}/toggle-active', [StafController::class, 'toggleActive'])
            ->name('staf.toggle-active');

        Route::get('staf/{user}/reset-password', [StafController::class, 'showResetPassword'])
            ->name('staf.reset-password');

        Route::post('staf/{user}/reset-password', [StafController::class, 'resetPassword'])
            ->name('staf.reset-password.store');
    });

    // [US-09] Kasir: input transaksi walk-in & galon baru
    Route::livewire('kasir/transaksi', 'pages::kasir.transaksi')->name('kasir.transaksi');

    // [US-10] Kasir: input pesanan COD manual
    Route::livewire('kasir/cod', 'pages::kasir.pesanan-cod')->name('kasir.pesanan-cod');

    // [US-12] Kasir & Admin Cabang: daftar pesanan, penugasan kurir, status, dan pembatalan
    Route::livewire('cabang/pesanan', 'pages::cabang.pesanan')->name('cabang.pesanan');

    // [US-15] Kasir & Admin Cabang: daftar transaksi cabang dengan filter
    Route::livewire('cabang/transaksi', 'pages::cabang.transaksi')->name('cabang.transaksi');
});

require __DIR__.'/settings.php';
