<?php

namespace Tests\Feature\Foundation;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\Role;
use App\Models\Cabang;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_user_belongs_to_a_cabang_and_casts_role(): void
    {
        $user = User::factory()->adminCabang()->create();

        $this->assertSame(Role::AdminCabang, $user->fresh()->role);
        $this->assertInstanceOf(Cabang::class, $user->cabang);
        $this->assertTrue($user->is_active);
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_super_admin_has_no_cabang(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->assertNull($user->cabang_id);
        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_role_and_cabang_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->kasir()->create();
        $cabangAsal = $user->cabang_id;

        $user->fill([
            'role' => Role::SuperAdmin->value,
            'cabang_id' => Cabang::factory()->create()->id,
        ])->save();

        $user->refresh();
        $this->assertSame(Role::Kasir, $user->role);
        $this->assertSame($cabangAsal, $user->cabang_id);
    }

    public function test_transaksi_stores_unit_price_and_total(): void
    {
        $transaksi = Transaksi::factory()->create(['jumlah' => 3]);

        $transaksi->refresh();
        $this->assertSame(Kanal::WalkIn, $transaksi->kanal);
        $this->assertSame(JenisItem::AirIsiUlang, $transaksi->jenis_item);
        $this->assertSame(MetodePembayaran::Tunai, $transaksi->metode_pembayaran);
        $this->assertSame(6000, $transaksi->harga_satuan);
        $this->assertSame(18000, $transaksi->total);
    }

    public function test_transaksi_pencatat_is_from_the_same_cabang(): void
    {
        $walkIn = Transaksi::factory()->create();
        $canvassing = Transaksi::factory()->canvassing()->create();

        $this->assertSame($walkIn->cabang_id, $walkIn->pencatat->cabang_id);
        $this->assertSame(Role::Kasir, $walkIn->pencatat->role);
        $this->assertSame($canvassing->cabang_id, $canvassing->pencatat->cabang_id);
        $this->assertSame(Role::Kurir, $canvassing->pencatat->role);
        $this->assertSame(7000, $canvassing->harga_satuan);
    }

    public function test_cabang_relations_only_return_their_own_records(): void
    {
        [$cabangA, $cabangB] = Cabang::factory()->count(2)->create();
        Transaksi::factory()->count(2)->for($cabangA)->create();
        Transaksi::factory()->for($cabangB)->create();

        $this->assertCount(2, $cabangA->transaksi);
        $this->assertCount(1, $cabangB->transaksi);
        $this->assertTrue($cabangA->users->every(fn (User $user) => $user->cabang_id === $cabangA->id));
    }

    public function test_belum_dibayar_state_leaves_dibayar_pada_empty(): void
    {
        $transaksi = Transaksi::factory()->cod()->belumDibayar()->create();

        $this->assertNull($transaksi->dibayar_pada);
        $this->assertSame(Kanal::Cod, $transaksi->kanal);
    }
}
