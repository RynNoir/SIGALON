<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk 2 cabang nyata CV. Aufa di Kota Padang (US-03).
 *
 * Dijalankan manual via: php artisan db:seed --class=CabangSeeder
 * Idempotent: menggunakan updateOrCreate dengan kunci nama cabang.
 */
class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'nama' => 'Cabang Dadok Tunggul Hitam',
                'alamat' => 'Jl. Utama Dadok Tunggul Hitam, Padang',
                // Catatan: Koordinat perkiraan untuk pengujian, perlu diverifikasi PO via Google Maps/OSM.
                'latitude' => -0.8715000,
                'longitude' => 100.3640000,
                'is_active' => true,
            ],
            [
                'nama' => 'Cabang Siteba',
                'alamat' => 'Jl. Siteba, Padang',
                // Catatan: Koordinat perkiraan untuk pengujian, perlu diverifikasi PO via Google Maps/OSM.
                'latitude' => -0.8980000,
                'longitude' => 100.3680000,
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Cabang::updateOrCreate(
                ['nama' => $branch['nama']],
                [
                    'alamat' => $branch['alamat'],
                    'latitude' => $branch['latitude'],
                    'longitude' => $branch['longitude'],
                    'is_active' => $branch['is_active'],
                ]
            );
        }
    }
}
