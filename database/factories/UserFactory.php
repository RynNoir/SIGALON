<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
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
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => Role::Kasir,
            'cabang_id' => Cabang::factory(),
            'is_active' => true,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::SuperAdmin,
            'cabang_id' => null,
        ]);
    }

    public function adminCabang(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::AdminCabang,
        ]);
    }

    public function kasir(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Kasir,
        ]);
    }

    public function kurir(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Kurir,
        ]);
    }

    public function pelanggan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Pelanggan,
            'cabang_id' => null,
            'phone' => '628'.fake()->unique()->numerify('##########'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
