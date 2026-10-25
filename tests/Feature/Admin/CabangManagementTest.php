<?php

namespace Tests\Feature\Admin;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CabangManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.cabang.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_non_super_admin_cannot_access_cabang_management(): void
    {
        $cabang = Cabang::factory()->create();
        $adminCabang = User::factory()->adminCabang()->for($cabang)->create();
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($adminCabang)
            ->get(route('admin.cabang.index'))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->get(route('admin.cabang.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_cabang_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create(['nama' => 'Cabang Panam Utama']);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.cabang.index'));

        $response->assertOk();
        $response->assertSee('Cabang Panam Utama');
        $response->assertSee('Kelola Cabang');
    }

    public function test_super_admin_can_view_create_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.cabang.create'));

        $response->assertOk();
        $response->assertSee('Tambah Cabang');
    }

    public function test_super_admin_can_create_new_cabang(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.cabang.store'), [
                'nama' => 'Cabang Tampan',
                'alamat' => 'Jl. HR Soebrantas No. 99, Pekanbaru',
                'latitude' => '0.4651234',
                'longitude' => '101.4012345',
            ]);

        $response->assertRedirect(route('admin.cabang.index'));
        $response->assertSessionHas('status', __('Data cabang berhasil ditambahkan.'));

        $this->assertDatabaseHas('cabang', [
            'nama' => 'Cabang Tampan',
            'alamat' => 'Jl. HR Soebrantas No. 99, Pekanbaru',
            'is_active' => true,
        ]);
    }

    public function test_create_cabang_validation_fails_when_fields_are_invalid(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.cabang.store'), [
                'nama' => '',
                'alamat' => '',
                'latitude' => 'bukan-angka',
                'longitude' => 'bukan-angka',
            ]);

        $response->assertSessionHasErrors(['nama', 'alamat', 'latitude', 'longitude']);
    }

    public function test_super_admin_can_view_edit_form(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create(['nama' => 'Cabang Rumbai']);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.cabang.edit', $cabang));

        $response->assertOk();
        $response->assertSee('Cabang Rumbai');
        $response->assertSee('Ubah Cabang');
    }

    public function test_super_admin_can_update_cabang(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create([
            'nama' => 'Cabang Lama',
            'alamat' => 'Alamat Lama',
        ]);

        $response = $this->actingAs($superAdmin)
            ->put(route('admin.cabang.update', $cabang), [
                'nama' => 'Cabang Baru',
                'alamat' => 'Alamat Baru No. 10',
                'latitude' => '0.5123456',
                'longitude' => '101.4567890',
            ]);

        $response->assertRedirect(route('admin.cabang.index'));
        $response->assertSessionHas('status', __('Data cabang berhasil diperbarui.'));

        $this->assertDatabaseHas('cabang', [
            'id' => $cabang->id,
            'nama' => 'Cabang Baru',
            'alamat' => 'Alamat Baru No. 10',
        ]);
    }

    public function test_cabang_name_is_escaped_in_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $cabang = Cabang::factory()->create([
            'nama' => '<script>alert("xss")</script>Cabang Aman',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.cabang.index'));

        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee(e($cabang->nama), false);
    }
}
