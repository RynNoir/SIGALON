<?php

namespace Database\Factories;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Default: akun nomor HP + password. Gunakan state google() untuk akun Google.
 *
 * @extends Factory<Pelanggan>
 */
class PelangganFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'no_hp' => '628'.fake()->unique()->numerify('##########'),
            'email' => null,
            'google_id' => null,
            'google_avatar' => null,
            'password' => static::$password ??= Hash::make('password'),
            'alamat' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Akun Google: nomor HP dan alamat dilengkapi saat pesanan pertama (ERD: Akun PELANGGAN).
     */
    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'no_hp' => null,
            'email' => fake()->unique()->safeEmail(),
            'google_id' => (string) fake()->unique()->numerify('#####################'),
            'password' => null,
        ]);
    }
}
