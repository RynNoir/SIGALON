<?php

use App\Http\Controllers\Admin\AdminCabangController;
use App\Http\Controllers\Admin\CabangController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

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
    });
});

require __DIR__.'/settings.php';
