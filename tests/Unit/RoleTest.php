<?php

namespace Tests\Unit;

use App\Enums\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_only_super_admin_works_without_cabang(): void
    {
        $this->assertFalse(Role::SuperAdmin->requiresCabang());
        $this->assertTrue(Role::AdminCabang->requiresCabang());
        $this->assertTrue(Role::Kasir->requiresCabang());
        $this->assertTrue(Role::Kurir->requiresCabang());
    }

    public function test_only_super_admin_and_admin_cabang_can_access_web(): void
    {
        $this->assertTrue(Role::SuperAdmin->canAccessWeb());
        $this->assertTrue(Role::AdminCabang->canAccessWeb());
        $this->assertFalse(Role::Kasir->canAccessWeb());
        $this->assertFalse(Role::Kurir->canAccessWeb());
    }

    public function test_roles_match_prd_values(): void
    {
        $this->assertSame(
            ['super_admin', 'admin_cabang', 'kasir', 'kurir'],
            array_column(Role::cases(), 'value'),
        );
    }
}
