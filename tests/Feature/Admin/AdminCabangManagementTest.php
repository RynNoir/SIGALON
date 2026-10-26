<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCabangManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.admin-cabang.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_super_admin_cannot_access_admin_cabang_management(): void
    {
        $cabang = Cabang::factory()->create();
        $adminCabang = User::factory()->adminCabang()->for($cabang)->create();
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($adminCabang)
            ->get(route('admin.admin-cabang.index'))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->get(route('admin.admin-cabang.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_admin_cabang_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create(['nama' => 'Cabang Sukajadi']);
        $admin = User::factory()->adminCabang()->for($cabang)->create([
            'name' => 'Ahmad Admin',
            'email' => 'ahmad@sigalon.test',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.admin-cabang.index'));

        $response->assertOk();
        $response->assertSee('Ahmad Admin');
        $response->assertSee('ahmad@sigalon.test');
        $response->assertSee('Cabang Sukajadi');
    }

    public function test_super_admin_can_view_create_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Cabang::factory()->create(['nama' => 'Cabang Marpoyan']);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.admin-cabang.create'));

        $response->assertOk();
        $response->assertSee('Tambah Akun Admin Cabang');
        $response->assertSee('Cabang Marpoyan');
    }

    public function test_super_admin_can_create_admin_cabang_account(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.admin-cabang.store'), [
                'name' => 'Admin Baru',
                'email' => 'admin.baru@sigalon.test',
                'password' => 'password123',
                'cabang_id' => $cabang->id,
            ]);

        $response->assertRedirect(route('admin.admin-cabang.index'));
        $response->assertSessionHas('status', __('Akun Admin Cabang berhasil dibuat.'));

        $createdUser = User::where('email', 'admin.baru@sigalon.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertSame('Admin Baru', $createdUser->name);
        $this->assertSame(Role::AdminCabang, $createdUser->role);
        $this->assertSame($cabang->id, $createdUser->cabang_id);
        $this->assertTrue($createdUser->is_active);
        $this->assertTrue(Hash::check('password123', $createdUser->password));
    }

    public function test_create_admin_cabang_validation_rules(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $existing = User::factory()->create(['email' => 'existing@sigalon.test']);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.admin-cabang.store'), [
                'name' => '',
                'email' => 'existing@sigalon.test',
                'password' => 'pendek',
                'cabang_id' => 99999,
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'cabang_id']);
    }

    public function test_super_admin_can_view_edit_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.admin-cabang.edit', $admin));

        $response->assertOk();
        $response->assertSee($admin->name);
        $response->assertSee($admin->email);
    }

    public function test_super_admin_can_update_admin_cabang_profile(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang1 = Cabang::factory()->create();
        $cabang2 = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang1)->create([
            'name' => 'Nama Lama',
            'email' => 'lama@sigalon.test',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('admin.admin-cabang.update', $admin), [
                'name' => 'Nama Baru',
                'email' => 'baru@sigalon.test',
                'cabang_id' => $cabang2->id,
            ]);

        $response->assertRedirect(route('admin.admin-cabang.index'));
        $response->assertSessionHas('status', __('Data Admin Cabang berhasil diperbarui.'));

        $admin->refresh();
        $this->assertSame('Nama Baru', $admin->name);
        $this->assertSame('baru@sigalon.test', $admin->email);
        $this->assertSame($cabang2->id, $admin->cabang_id);
    }

    public function test_super_admin_can_toggle_admin_cabang_active_status(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create(['is_active' => true]);

        // Nonaktifkan akun
        $response = $this->actingAs($superAdmin)
            ->post(route('admin.admin-cabang.toggle-active', $admin));

        $response->assertRedirect(route('admin.admin-cabang.index'));
        $response->assertSessionHas('status', __('Akun Admin Cabang berhasil dinonaktifkan.'));

        $admin->refresh();
        $this->assertFalse($admin->is_active);
        $this->assertDatabaseHas('users', ['id' => $admin->id]); // Memastikan akun tidak dihapus

        // Aktifkan kembali akun
        $response = $this->actingAs($superAdmin)
            ->post(route('admin.admin-cabang.toggle-active', $admin));

        $response->assertRedirect(route('admin.admin-cabang.index'));
        $response->assertSessionHas('status', __('Akun Admin Cabang berhasil diaktifkan.'));

        $admin->refresh();
        $this->assertTrue($admin->is_active);
    }

    public function test_super_admin_can_view_reset_password_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.admin-cabang.reset-password', $admin));

        $response->assertOk();
        $response->assertSee('Reset Kata Sandi Admin Cabang');
        $response->assertSee($admin->name);
    }

    public function test_super_admin_can_reset_admin_cabang_password(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create([
            'password' => Hash::make('password_lama123'),
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.admin-cabang.reset-password.store', $admin), [
                'password' => 'password_baru456',
                'password_confirmation' => 'password_baru456',
            ]);

        $response->assertRedirect(route('admin.admin-cabang.index'));
        $response->assertSessionHas('status', __('Kata sandi Admin Cabang berhasil direset.'));

        $admin->refresh();
        $this->assertTrue(Hash::check('password_baru456', $admin->password));
    }

    public function test_reset_password_validation_fails_for_short_or_unconfirmed_password(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.admin-cabang.reset-password.store', $admin), [
                'password' => 'pendek',
                'password_confirmation' => 'berbeda123',
            ]);

        $response->assertSessionHasErrors(['password']);
    }
}
