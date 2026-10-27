<?php

namespace Tests\Feature\Cabang;

use App\Enums\Role;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StafManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('cabang.staf.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cabang_cannot_access_staf_management(): void
    {
        $cabang = Cabang::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $kasir = User::factory()->kasir()->for($cabang)->create();
        $kurir = User::factory()->kurir()->for($cabang)->create();

        $this->actingAs($superAdmin)
            ->get(route('cabang.staf.index'))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->get(route('cabang.staf.index'))
            ->assertForbidden();

        // Kurir is rejected by EnsureStaffCanAccessWeb middleware and redirected to akses-ditolak
        $this->actingAs($kurir)
            ->get(route('cabang.staf.index'))
            ->assertRedirect(route('akses-ditolak'));
    }

    public function test_admin_cabang_without_cabang_id_is_redirected_to_login(): void
    {
        $adminTanpaCabang = User::factory()->adminCabang()->create([
            'cabang_id' => null,
        ]);

        // Staff requiring cabang without cabang_id is logged out to login page by middleware
        $this->actingAs($adminTanpaCabang)
            ->get(route('cabang.staf.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_cabang_can_view_staf_index_with_own_branch_staf(): void
    {
        $cabang = Cabang::factory()->create(['nama' => 'Cabang Tampan']);
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $kasir = User::factory()->kasir()->for($cabang)->create([
            'name' => 'Siti Kasir',
            'email' => 'siti@sigalon.test',
        ]);
        $kurir = User::factory()->kurir()->for($cabang)->create([
            'name' => 'Budi Kurir',
            'email' => 'budi@sigalon.test',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('cabang.staf.index'));

        $response->assertOk();
        $response->assertSee('Kelola Staf Cabang');
        $response->assertSee('Cabang Tampan');
        $response->assertSee('Siti Kasir');
        $response->assertSee('siti@sigalon.test');
        $response->assertSee('Kasir');
        $response->assertSee('Budi Kurir');
        $response->assertSee('budi@sigalon.test');
        $response->assertSee('Kurir');
    }

    public function test_admin_cabang_cannot_see_staf_from_other_branches(): void
    {
        $cabangA = Cabang::factory()->create(['nama' => 'Cabang A']);
        $cabangB = Cabang::factory()->create(['nama' => 'Cabang B']);

        $adminA = User::factory()->adminCabang()->for($cabangA)->create();
        $kasirA = User::factory()->kasir()->for($cabangA)->create(['name' => 'Kasir Cabang A']);
        $kasirB = User::factory()->kasir()->for($cabangB)->create(['name' => 'Kasir Cabang B']);

        $response = $this->actingAs($adminA)
            ->get(route('cabang.staf.index'));

        $response->assertOk();
        $response->assertSee('Kasir Cabang A');
        $response->assertDontSee('Kasir Cabang B');
    }

    public function test_admin_cabang_cannot_see_super_admin_or_admin_cabang_in_staf_list(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create(['name' => 'Admin Utama']);
        $adminLain = User::factory()->adminCabang()->for($cabang)->create(['name' => 'Admin Kedua']);
        $superAdmin = User::factory()->superAdmin()->create(['name' => 'Super Admin Boss']);

        $response = $this->actingAs($admin)
            ->get(route('cabang.staf.index'));

        $response->assertOk();
        $response->assertDontSee('Admin Kedua');
        $response->assertDontSee('Super Admin Boss');
    }

    public function test_empty_state_displayed_when_no_staf_in_branch(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response = $this->actingAs($admin)
            ->get(route('cabang.staf.index'));

        $response->assertOk();
        $response->assertSee('Belum ada staf Kasir atau Kurir');
    }

    public function test_admin_cabang_can_view_create_form(): void
    {
        $cabang = Cabang::factory()->create(['nama' => 'Cabang Rumbai']);
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response = $this->actingAs($admin)
            ->get(route('cabang.staf.create'));

        $response->assertOk();
        $response->assertSee('Tambah Staf Cabang');
        $response->assertSee('Cabang Rumbai');
        $response->assertSee('Kasir (Website Staf)');
        $response->assertSee('Kurir (Aplikasi Android)');
    }

    public function test_admin_cabang_can_create_kasir_account(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response = $this->actingAs($admin)
            ->post(route('cabang.staf.store'), [
                'name' => 'Kasir Baru',
                'email' => 'kasir.baru@sigalon.test',
                'password' => 'password123',
                'role' => Role::Kasir->value,
            ]);

        $response->assertRedirect(route('cabang.staf.index'));
        $response->assertSessionHas('status', __('Akun staf berhasil dibuat.'));

        $created = User::where('email', 'kasir.baru@sigalon.test')->first();
        $this->assertNotNull($created);
        $this->assertSame('Kasir Baru', $created->name);
        $this->assertSame(Role::Kasir, $created->role);
        $this->assertSame($cabang->id, $created->cabang_id);
        $this->assertTrue($created->is_active);
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_admin_cabang_can_create_kurir_account(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response = $this->actingAs($admin)
            ->post(route('cabang.staf.store'), [
                'name' => 'Kurir Baru',
                'email' => 'kurir.baru@sigalon.test',
                'password' => 'password123',
                'role' => Role::Kurir->value,
            ]);

        $response->assertRedirect(route('cabang.staf.index'));
        $response->assertSessionHas('status', __('Akun staf berhasil dibuat.'));

        $created = User::where('email', 'kurir.baru@sigalon.test')->first();
        $this->assertNotNull($created);
        $this->assertSame('Kurir Baru', $created->name);
        $this->assertSame(Role::Kurir, $created->role);
        $this->assertSame($cabang->id, $created->cabang_id);
    }

    public function test_create_staf_validation_rules(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $existing = User::factory()->kasir()->for($cabang)->create(['email' => 'ada@sigalon.test']);

        $response = $this->actingAs($admin)
            ->post(route('cabang.staf.store'), [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'role' => 'invalid-role',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);

        $responseDup = $this->actingAs($admin)
            ->post(route('cabang.staf.store'), [
                'name' => 'Staf Baru',
                'email' => 'ada@sigalon.test',
                'password' => 'password123',
                'role' => Role::Kasir->value,
            ]);

        $responseDup->assertSessionHasErrors(['email']);
    }

    public function test_cannot_create_staf_with_super_admin_or_admin_cabang_role(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $response1 = $this->actingAs($admin)
            ->post(route('cabang.staf.store'), [
                'name' => 'Impostor Super',
                'email' => 'impostor1@sigalon.test',
                'password' => 'password123',
                'role' => Role::SuperAdmin->value,
            ]);

        $response1->assertSessionHasErrors(['role']);

        $response2 = $this->actingAs($admin)
            ->post(route('cabang.staf.store'), [
                'name' => 'Impostor Admin',
                'email' => 'impostor2@sigalon.test',
                'password' => 'password123',
                'role' => Role::AdminCabang->value,
            ]);

        $response2->assertSessionHasErrors(['role']);

        $this->assertDatabaseMissing('users', ['email' => 'impostor1@sigalon.test']);
        $this->assertDatabaseMissing('users', ['email' => 'impostor2@sigalon.test']);
    }

    public function test_admin_cabang_can_view_edit_form_for_own_branch_staf(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $kasir = User::factory()->kasir()->for($cabang)->create([
            'name' => 'Kasir Edit',
            'email' => 'kasiredit@sigalon.test',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('cabang.staf.edit', $kasir));

        $response->assertOk();
        $response->assertSee('Ubah Staf Cabang');
        $response->assertSee('Kasir Edit');
        $response->assertSee('kasiredit@sigalon.test');
    }

    public function test_admin_cabang_cannot_view_edit_form_for_staf_from_other_branch(): void
    {
        $cabangA = Cabang::factory()->create();
        $cabangB = Cabang::factory()->create();

        $adminA = User::factory()->adminCabang()->for($cabangA)->create();
        $kasirB = User::factory()->kasir()->for($cabangB)->create();

        $this->actingAs($adminA)
            ->get(route('cabang.staf.edit', $kasirB))
            ->assertForbidden();
    }

    public function test_admin_cabang_cannot_view_edit_form_for_non_kasir_kurir(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $otherAdmin = User::factory()->adminCabang()->for($cabang)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('cabang.staf.edit', $otherAdmin))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('cabang.staf.edit', $superAdmin))
            ->assertNotFound();
    }

    public function test_admin_cabang_can_update_staf(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $staf = User::factory()->kasir()->for($cabang)->create([
            'name' => 'Nama Lama',
            'email' => 'lama@sigalon.test',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('cabang.staf.update', $staf), [
                'name' => 'Nama Baru',
                'email' => 'baru@sigalon.test',
                'role' => Role::Kurir->value,
            ]);

        $response->assertRedirect(route('cabang.staf.index'));
        $response->assertSessionHas('status', __('Data staf berhasil diperbarui.'));

        $staf->refresh();
        $this->assertSame('Nama Baru', $staf->name);
        $this->assertSame('baru@sigalon.test', $staf->email);
        $this->assertSame(Role::Kurir, $staf->role);
        $this->assertSame($cabang->id, $staf->cabang_id);
    }

    public function test_admin_cabang_cannot_update_staf_from_other_branch(): void
    {
        $cabangA = Cabang::factory()->create();
        $cabangB = Cabang::factory()->create();

        $adminA = User::factory()->adminCabang()->for($cabangA)->create();
        $stafB = User::factory()->kasir()->for($cabangB)->create([
            'name' => 'Nama Asli',
        ]);

        $this->actingAs($adminA)
            ->put(route('cabang.staf.update', $stafB), [
                'name' => 'Nama Hacked',
                'email' => 'hacked@sigalon.test',
                'role' => Role::Kasir->value,
            ])
            ->assertForbidden();

        $stafB->refresh();
        $this->assertSame('Nama Asli', $stafB->name);
    }

    public function test_admin_cabang_can_toggle_staf_active_status(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $staf = User::factory()->kasir()->for($cabang)->create(['is_active' => true]);

        // Toggle to inactive
        $response = $this->actingAs($admin)
            ->post(route('cabang.staf.toggle-active', $staf));

        $response->assertRedirect(route('cabang.staf.index'));
        $response->assertSessionHas('status', __('Akun staf berhasil dinonaktifkan.'));
        $this->assertFalse($staf->fresh()->is_active);

        // Toggle back to active
        $response2 = $this->actingAs($admin)
            ->post(route('cabang.staf.toggle-active', $staf));

        $response2->assertRedirect(route('cabang.staf.index'));
        $response2->assertSessionHas('status', __('Akun staf berhasil diaktifkan.'));
        $this->assertTrue($staf->fresh()->is_active);
    }

    public function test_admin_cabang_cannot_toggle_staf_from_other_branch(): void
    {
        $cabangA = Cabang::factory()->create();
        $cabangB = Cabang::factory()->create();

        $adminA = User::factory()->adminCabang()->for($cabangA)->create();
        $stafB = User::factory()->kasir()->for($cabangB)->create(['is_active' => true]);

        $this->actingAs($adminA)
            ->post(route('cabang.staf.toggle-active', $stafB))
            ->assertForbidden();

        $this->assertTrue($stafB->fresh()->is_active);
    }

    public function test_deactivated_staf_cannot_access_staff_web(): void
    {
        $cabang = Cabang::factory()->create();
        $kasir = User::factory()->kasir()->for($cabang)->create(['is_active' => false]);

        $this->assertFalse($kasir->canAccessStaffWeb());
    }

    public function test_admin_cabang_can_view_reset_password_form(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $staf = User::factory()->kasir()->for($cabang)->create([
            'name' => 'Rian Kasir',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('cabang.staf.reset-password', $staf));

        $response->assertOk();
        $response->assertSee('Reset Kata Sandi Staf');
        $response->assertSee('Rian Kasir');
    }

    public function test_admin_cabang_can_reset_staf_password(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $staf = User::factory()->kasir()->for($cabang)->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('cabang.staf.reset-password.store', $staf), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('cabang.staf.index'));
        $response->assertSessionHas('status', __('Kata sandi staf berhasil direset.'));

        $this->assertTrue(Hash::check('newpassword123', $staf->fresh()->password));
    }

    public function test_admin_cabang_cannot_reset_password_for_staf_from_other_branch(): void
    {
        $cabangA = Cabang::factory()->create();
        $cabangB = Cabang::factory()->create();

        $adminA = User::factory()->adminCabang()->for($cabangA)->create();
        $stafB = User::factory()->kasir()->for($cabangB)->create([
            'password' => Hash::make('originalpassword'),
        ]);

        $this->actingAs($adminA)
            ->post(route('cabang.staf.reset-password.store', $stafB), [
                'password' => 'hackedpassword123',
                'password_confirmation' => 'hackedpassword123',
            ])
            ->assertForbidden();

        $this->assertTrue(Hash::check('originalpassword', $stafB->fresh()->password));
    }

    public function test_xss_in_staf_name_is_escaped(): void
    {
        $cabang = Cabang::factory()->create();
        $admin = User::factory()->adminCabang()->for($cabang)->create();
        $xssName = '<script>alert("xss")</script>';

        User::factory()->kasir()->for($cabang)->create([
            'name' => $xssName,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('cabang.staf.index'));

        $response->assertOk();
        $response->assertDontSee($xssName, false);
        $response->assertSee(e($xssName), false);
    }
}
