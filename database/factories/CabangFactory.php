<?php

namespace Database\Factories;

use App\Models\Cabang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Data fiktif untuk tes. Data cabang asli diisi dari mitra (US-03), bukan dari factory ini.
 *
 * @extends Factory<Cabang>
 */
class CabangFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => 'Cabang '.fake()->unique()->city(),
            'alamat' => fake()->address(),
            'latitude' => fake()->latitude(-1.2, -0.6),
            'longitude' => fake()->longitude(100.2, 100.6),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
