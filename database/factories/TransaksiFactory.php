<?php

namespace Database\Factories;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Models\Cabang;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaksi>
 */
class TransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cabang_id' => Cabang::factory(),
            'dicatat_oleh' => fn (array $attributes) => User::factory()->kasir()->state([
                'cabang_id' => $attributes['cabang_id'],
            ]),
            'kanal' => Kanal::WalkIn,
            'jenis_item' => JenisItem::AirIsiUlang,
            'jumlah' => fake()->numberBetween(1, 10),
            'harga_satuan' => 6000,
            'total' => fn (array $attributes) => $attributes['jumlah'] * $attributes['harga_satuan'],
            'metode_pembayaran' => MetodePembayaran::Tunai,
            'dibayar_pada' => now(),
        ];
    }

    public function cod(): static
    {
        return $this->state(fn (array $attributes) => [
            'kanal' => Kanal::Cod,
            'harga_satuan' => 7000,
        ]);
    }

    public function canvassing(): static
    {
        return $this->state(fn (array $attributes) => [
            'kanal' => Kanal::Canvassing,
            'harga_satuan' => 7000,
            'dicatat_oleh' => fn (array $attributes) => User::factory()->kurir()->state([
                'cabang_id' => $attributes['cabang_id'],
            ]),
        ]);
    }

    public function belumDibayar(): static
    {
        return $this->state(fn (array $attributes) => [
            'dibayar_pada' => null,
        ]);
    }
}
