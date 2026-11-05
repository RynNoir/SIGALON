<?php

namespace Database\Factories;

use App\Enums\StatusTransaksi;
use App\Models\RiwayatStatus;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Default: status awal pesanan COD yang dicatat Kasir.
 *
 * @extends Factory<RiwayatStatus>
 */
class RiwayatStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaksi_id' => Transaksi::factory()->cod(StatusTransaksi::Diproses),
            'status_lama' => null,
            'status_baru' => StatusTransaksi::Diproses,
            'diubah_oleh_staff' => User::factory()->kasir(),
            'diubah_oleh_pelanggan' => null,
            'catatan' => null,
        ];
    }
}
