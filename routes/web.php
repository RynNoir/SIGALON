<?php

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
    Route::livewire('dashboard/cabang/{cabang}', 'pages::dashboard-cabang')->name('dashboard.cabang');
});

require __DIR__.'/settings.php';
