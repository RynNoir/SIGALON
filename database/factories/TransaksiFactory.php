<?php

namespace Database\Factories;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Default: walk-in isi ulang yang sudah selesai hari ini, harga dari config/sigalon.php.
 *
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
            'pelanggan_id' => null,
            'kurir_id' => null,
            'jenis_kanal' => Kanal::WalkIn,
            'jenis_item' => JenisItem::IsiUlang,
            'jumlah_galon' => fake()->numberBetween(1, 10),
            'total_harga' => fn (array $attributes) => $attributes['jumlah_galon'] * $this->hargaSatuan($attributes),
            'metode_bayar' => MetodePembayaran::Tunai,
            'status' => StatusTransaksi::Selesai,
            'tanggal' => now()->toDateString(),
            'nama_pemesan' => null,
            'no_hp_pemesan' => null,
            'alamat_antar' => null,
            'dicatat_oleh' => fn (array $attributes) => User::factory()->kasir()->state([
                'cabang_id' => $attributes['cabang_id'],
            ]),
            'diverifikasi_oleh' => null,
        ];
    }

    /**
     * COD manual (tanpa akun pelanggan); status default `selesai`.
     */
    public function cod(StatusTransaksi $status = StatusTransaksi::Selesai): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_kanal' => Kanal::Cod,
            'jenis_item' => JenisItem::IsiUlang,
            'status' => $status,
            'nama_pemesan' => fake()->name(),
            'no_hp_pemesan' => '628'.fake()->numerify('##########'),
            'alamat_antar' => fake()->address(),
            'kurir_id' => fn (array $attributes) => User::factory()->kurir()->state([
                'cabang_id' => $attributes['cabang_id'],
            ]),
        ]);
    }

    /**
     * Canvassing dicatat kurir; status default `tervalidasi`.
     */
    public function canvassing(StatusTransaksi $status = StatusTransaksi::Tervalidasi): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_kanal' => Kanal::Canvassing,
            'jenis_item' => JenisItem::IsiUlang,
            'status' => $status,
            'kurir_id' => fn (array $attributes) => User::factory()->kurir()->state([
                'cabang_id' => $attributes['cabang_id'],
            ]),
            'dicatat_oleh' => fn (array $attributes) => $attributes['kurir_id'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function hargaSatuan(array $attributes): int
    {
        $kanal = $attributes['jenis_kanal'] instanceof Kanal ? $attributes['jenis_kanal'] : Kanal::from($attributes['jenis_kanal']);
        $item = $attributes['jenis_item'] instanceof JenisItem ? $attributes['jenis_item'] : JenisItem::from($attributes['jenis_item']);

        return match (true) {
            $item === JenisItem::GalonBaru => (int) config('sigalon.harga.galon_baru'),
            $kanal === Kanal::WalkIn => (int) config('sigalon.harga.walk_in'),
            default => (int) config('sigalon.harga.cod'),
        };
    }
}
