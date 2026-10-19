<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Data cabang tidak di-seed di sini: koordinat dan alamat asli menunggu data dari mitra (US-03).
     */
    public function run(): void
    {
        $this->seedSuperAdmin();
    }

    private function seedSuperAdmin(): void
    {
        $email = config('sigalon.seed.superadmin_email');

        if (blank($email)) {
            $this->command->warn('SEED_SUPERADMIN_EMAIL kosong; akun Super Admin tidak dibuat.');

            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command->info("Super Admin {$email} sudah ada; dilewati.");

            return;
        }

        $password = config('sigalon.seed.superadmin_password');
        $generated = blank($password);

        if ($generated) {
            $password = Str::password(16);
        }

        $user = new User([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => $password,
        ]);
        $user->role = Role::SuperAdmin;
        $user->email_verified_at = now();
        $user->save();

        $this->command->info("Super Admin dibuat: {$email}");

        if ($generated) {
            $this->command->warn("Password acak (simpan sekarang, tidak ditampilkan lagi): {$password}");
        }
    }
}
