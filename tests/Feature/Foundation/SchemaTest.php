<?php

namespace Tests\Feature\Foundation;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
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

    public function test_transaksi_follows_erd_columns_and_stores_total_harga(): void
    {
        $transaksi = Transaksi::factory()->create(['jumlah_galon' => 3]);

        $transaksi->refresh();
        $this->assertSame(Kanal::WalkIn, $transaksi->jenis_kanal);
        $this->assertSame(JenisItem::IsiUlang, $transaksi->jenis_item);
        $this->assertSame(MetodePembayaran::Tunai, $transaksi->metode_bayar);
        $this->assertSame(StatusTransaksi::Selesai, $transaksi->status);
        $this->assertSame(18000, $transaksi->total_harga);
        $this->assertNull($transaksi->pelanggan_id);
    }

    public function test_transaksi_staff_relations_are_from_the_same_cabang(): void
    {
        $walkIn = Transaksi::factory()->create();
        $cod = Transaksi::factory()->cod()->create(['jumlah_galon' => 2]);
        $canvassing = Transaksi::factory()->canvassing()->create();

        $this->assertSame($walkIn->cabang_id, $walkIn->pencatat?->cabang_id);
        $this->assertSame(Role::Kasir, $walkIn->pencatat?->role);
        $this->assertSame(Role::Kurir, $cod->kurir?->role);
        $this->assertSame(14000, $cod->total_harga);
        $this->assertSame($canvassing->kurir_id, $canvassing->dicatat_oleh);
        $this->assertSame($canvassing->cabang_id, $canvassing->kurir?->cabang_id);
    }

    public function test_only_finished_and_validated_transactions_are_counted(): void
    {
        $walkIn = Transaksi::factory()->create();
        $codSelesai = Transaksi::factory()->cod()->create();
        $canvassingSah = Transaksi::factory()->canvassing()->create();
        Transaksi::factory()->cod(StatusTransaksi::Diproses)->create();
        Transaksi::factory()->cod(StatusTransaksi::Dibatalkan)->create();
        Transaksi::factory()->canvassing(StatusTransaksi::MenungguVerifikasi)->create();
        Transaksi::factory()->canvassing(StatusTransaksi::Ditolak)->create();

        $this->assertEqualsCanonicalizing(
            [$walkIn->id, $codSelesai->id, $canvassingSah->id],
            Transaksi::query()->sah()->pluck('id')->all(),
        );
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
}
