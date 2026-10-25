<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Cabang;
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
     * Urutan: 2 cabang pilot (CabangSeeder), lalu 1 akun default per peran staf untuk pengembangan lokal.
     */
    public function run(): void
    {
        $this->call(CabangSeeder::class);

        if (app()->isProduction()) {
            $this->command->warn('Environment production: akun default tidak dibuat.');

            return;
        }

        $this->seedAkunDefault();
    }

    private function seedAkunDefault(): void
    {
        $cabang = Cabang::query()->orderBy('id')->first();

        $akun = [
            ['email' => (string) (config('sigalon.seed.superadmin_email') ?: 'superadmin@sigalon.test'), 'name' => 'Super Admin (dev)', 'role' => Role::SuperAdmin, 'cabang_id' => null],
            ['email' => 'admin.cabang@sigalon.test', 'name' => 'Admin Cabang (dev)', 'role' => Role::AdminCabang, 'cabang_id' => $cabang?->id],
            ['email' => 'kasir@sigalon.test', 'name' => 'Kasir (dev)', 'role' => Role::Kasir, 'cabang_id' => $cabang?->id],
            ['email' => 'kurir@sigalon.test', 'name' => 'Kurir (dev)', 'role' => Role::Kurir, 'cabang_id' => $cabang?->id],
        ];

        $password = config('sigalon.seed.default_password');
        $generated = blank($password);

        if ($generated) {
            $password = Str::password(16);
        }

        $dibuat = [];

        foreach ($akun as $data) {
            if ($data['role']->requiresCabang() && $data['cabang_id'] === null) {
                $this->command->warn("Belum ada cabang; akun {$data['role']->label()} dilewati.");

                continue;
            }

            if (User::where('email', $data['email'])->exists()) {
                $this->command->info("Akun {$data['email']} sudah ada; dilewati.");

                continue;
            }

            $user = new User([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password,
            ]);
            $user->role = $data['role'];
            $user->cabang_id = $data['cabang_id'];
            $user->is_active = true;
            $user->email_verified_at = now();
            $user->save();

            $dibuat[] = [$data['role']->label(), $data['email'], $cabang && $data['cabang_id'] ? $cabang->nama : '-'];
        }

        if ($dibuat === []) {
            return;
        }

        $this->command->table(['Peran', 'Email', 'Cabang'], $dibuat);

        if ($generated) {
            $this->command->warn("Password acak untuk akun di atas (simpan sekarang, tidak ditampilkan lagi): {$password}");
        } else {
            $this->command->info('Password akun di atas memakai SEED_DEFAULT_PASSWORD dari .env.');
        }
    }
}
