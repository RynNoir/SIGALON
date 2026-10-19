<?php

namespace Tests\Feature\Foundation;

use App\Enums\Role;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_super_admin_from_config(): void
    {
        config([
            'sigalon.seed.superadmin_email' => 'superadmin@sigalon.test',
            'sigalon.seed.superadmin_password' => 'rahasia-lokal-123',
        ]);

        $this->seed();

        $user = User::where('email', 'superadmin@sigalon.test')->firstOrFail();
        $this->assertSame(Role::SuperAdmin, $user->role);
        $this->assertNull($user->cabang_id);
        $this->assertTrue(Hash::check('rahasia-lokal-123', $user->password));
        $this->assertSame(0, Cabang::count());
    }

    public function test_it_is_idempotent(): void
    {
        config(['sigalon.seed.superadmin_email' => 'superadmin@sigalon.test']);

        $this->seed();
        $this->seed();

        $this->assertSame(1, User::where('email', 'superadmin@sigalon.test')->count());
    }

    public function test_it_skips_when_email_is_not_configured(): void
    {
        config(['sigalon.seed.superadmin_email' => null]);

        $this->seed();

        $this->assertSame(0, User::count());
    }
}
