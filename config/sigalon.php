<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seeder Lokal
    |--------------------------------------------------------------------------
    |
    | Akun default untuk pengembangan dan pengujian lokal: Super Admin, Admin
    | Cabang, Kasir, dan Kurir (email ber-domain .test). Password diisi di .env
    | masing-masing developer dan tidak pernah disimpan ke repository; jika
    | kosong, seeder membuat password acak dan menampilkannya sekali.
    | Akun default tidak dibuat di environment production.
    |
    */

    'seed' => [
        'superadmin_email' => env('SEED_SUPERADMIN_EMAIL', 'superadmin@sigalon.test'),
        'default_password' => env('SEED_DEFAULT_PASSWORD', env('SEED_SUPERADMIN_PASSWORD')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tarif dan Harga Layanan
    |--------------------------------------------------------------------------
    |
    | Satu-satunya sumber harga aplikasi (PRD §6): tidak ada tabel atau menu harga.
    | Mengubah harga berarti mengubah file ini (atau .env) lalu deploy ulang.
    | - Walk-in: Rp6.000 / galon
    | - COD / Antar-Jemput dan canvassing: Rp7.000 / galon
    | - Galon kosong baru: nilai sementara sampai dikonfirmasi mitra; HARGA_GALON_BARU
    |   kosong memakai 45000, bernilai 0 menyembunyikan kartu galon baru di landing page.
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
