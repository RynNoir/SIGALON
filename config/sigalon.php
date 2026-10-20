<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seeder Lokal
    |--------------------------------------------------------------------------
    |
    | Akun Super Admin awal untuk lingkungan pengembangan. Nilai diisi di .env
    | masing-masing developer dan tidak pernah disimpan ke repository.
    |
    */

    'seed' => [
        'superadmin_email' => env('SEED_SUPERADMIN_EMAIL'),
        'superadmin_password' => env('SEED_SUPERADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tarif dan Harga Layanan
    |--------------------------------------------------------------------------
    |
    | Harga air isi ulang resmi (PRD §6):
    | - Walk-in: Rp6.000 / galon
    | - COD / Antar-Jemput: Rp7.000 / galon
    | - Galon kosong baru: dikonfigurasi Super Admin (nullable jika belum diset)
    |
    */
    'harga' => [
        'walk_in' => 6000,
        'cod' => 7000,
        'galon_baru' => env('HARGA_GALON_BARU') !== null && env('HARGA_GALON_BARU') !== ''
            ? ((int) env('HARGA_GALON_BARU') > 0 ? (int) env('HARGA_GALON_BARU') : null)
            : 45000,
    ],

];
