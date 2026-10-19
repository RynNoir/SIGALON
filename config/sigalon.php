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

];
