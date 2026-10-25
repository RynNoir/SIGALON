<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Kanal;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanggal tetap di tengah bulan agar "hari ini" dan "bulan ini" tidak bergantung pada tanggal tes dijalankan.
        $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00:00'));
        config(['sigalon.harga.walk_in' => 6000, 'sigalon.harga.cod' => 7000]);
    }

    public function test_super_admin_sees_totals_per_channel_and_per_branch_for_today(): void
    {
        [$cabangA, $cabangB] = $this->duaCabang();

        Transaksi::factory()->for($cabangA)->create(['jumlah_galon' => 3]);                 // walk-in Rp18.000
        Transaksi::factory()->for($cabangA)->cod()->create(['jumlah_galon' => 2]);          // COD Rp14.000
        Transaksi::factory()->for($cabangA)->canvassing()->create(['jumlah_galon' => 4]);   // canvassing Rp28.000
        Transaksi::factory()->for($cabangB)->create(['jumlah_galon' => 1]);                 // walk-in Rp6.000

        $ringkasan = $this->ringkasanSebagai(User::factory()->superAdmin()->create());

        $this->assertSame(66000, $ringkasan['totalPendapatan']);
        $this->assertSame(4, $ringkasan['totalTransaksi']);

        $perKanal = collect($ringkasan['perKanal'])->keyBy(fn (array $item) => $item['kanal']->value);
        $this->assertSame(2, $perKanal[Kanal::WalkIn->value]['jumlah']);
        $this->assertSame(24000, $perKanal[Kanal::WalkIn->value]['pendapatan']);
        $this->assertSame(14000, $perKanal[Kanal::Cod->value]['pendapatan']);
        $this->assertSame(28000, $perKanal[Kanal::Canvassing->value]['pendapatan']);
        $this->assertSame(42, $perKanal[Kanal::Canvassing->value]['persen']);

        $perCabang = collect($ringkasan['perCabang'])->keyBy('nama');
        $this->assertSame(60000, $perCabang['Cabang Uji A']['pendapatan']);
        $this->assertSame(3, $perCabang['Cabang Uji A']['jumlah']);
        $this->assertSame(6000, $perCabang['Cabang Uji B']['pendapatan']);
        $this->assertSame(['jumlah' => 1, 'pendapatan' => 6000], $perCabang['Cabang Uji B']['kanal'][Kanal::WalkIn->value]);
    }

    public function test_only_finished_cod_and_validated_canvassing_are_counted(): void
    {
        [$cabang] = $this->duaCabang();

        Transaksi::factory()->for($cabang)->cod()->create(['jumlah_galon' => 1]);
        Transaksi::factory()->for($cabang)->cod(StatusTransaksi::Diproses)->create();
        Transaksi::factory()->for($cabang)->cod(StatusTransaksi::Diantar)->create();
        Transaksi::factory()->for($cabang)->cod(StatusTransaksi::Dibatalkan)->create();
        Transaksi::factory()->for($cabang)->canvassing(StatusTransaksi::MenungguVerifikasi)->create();
        Transaksi::factory()->for($cabang)->canvassing(StatusTransaksi::Ditolak)->create();

        $ringkasan = $this->ringkasanSebagai(User::factory()->superAdmin()->create());

        $this->assertSame(1, $ringkasan['totalTransaksi']);
        $this->assertSame(7000, $ringkasan['totalPendapatan']);
    }

    public function test_period_today_excludes_earlier_days_and_month_includes_them(): void
    {
        [$cabang] = $this->duaCabang();

        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 1]);                                  // hari ini
        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 2, 'tanggal' => '2026-10-01']);       // awal bulan
        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 5, 'tanggal' => '2026-09-30']);       // bulan lalu

        $superAdmin = User::factory()->superAdmin()->create();

        $this->assertSame(6000, $this->ringkasanSebagai($superAdmin)['totalPendapatan']);
        $this->assertSame(18000, $this->ringkasanSebagai($superAdmin, 'bulan_ini')['totalPendapatan']);
    }

    public function test_dashboard_lists_all_thirteen_branches(): void
    {
        Cabang::factory()->count(13)->create();

        $this->actingAs(User::factory()->superAdmin()->create());

        $ringkasan = Livewire::test('pages::dashboard')->instance()->ringkasan;

        $this->assertCount(13, $ringkasan['perCabang']);
        Livewire::test('pages::dashboard')->assertSeeHtml('data-test="jumlah-cabang">13<');
    }

    public function test_super_admin_page_renders_figures_and_period_label(): void
    {
        [$cabang] = $this->duaCabang();
        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 2]);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dasbor Super Admin')
            ->assertSee('Hari ini, 20 Oktober 2026')
            ->assertSee('Rp12.000')
            ->assertSee('Cabang Uji A')
            ->assertDontSee('data-test="dashboard-empty"', false);
    }

    public function test_empty_state_is_shown_when_no_valid_transactions_exist(): void
    {
        $this->duaCabang();

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada transaksi sah pada periode ini')
            ->assertSee('Rp0');
    }

    public function test_invalid_period_falls_back_to_today(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::withQueryParams(['periode' => 'semua_waktu'])
            ->test('pages::dashboard')
            ->assertSet('periode', 'hari_ini')
            ->set('periode', '<script>')
            ->assertSet('periode', 'hari_ini');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function perannyaCabang(): array
    {
        return [
            'admin cabang' => ['adminCabang'],
            'kasir' => ['kasir'],
        ];
    }

    #[DataProvider('perannyaCabang')]
    public function test_branch_staff_never_receive_cross_branch_figures(string $state): void
    {
        [$cabangSendiri, $cabangLain] = $this->duaCabang();
        Transaksi::factory()->for($cabangLain)->create(['jumlah_galon' => 9]); // Rp54.000

        $staf = User::factory()->{$state}()->for($cabangSendiri)->create();

        $this->assertNull($this->ringkasanSebagai($staf));

        $this->actingAs($staf)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan cabang Anda')
            ->assertDontSee('Cabang Uji B')
            ->assertDontSee('Rp54.000')
            ->assertDontSee('Dasbor Super Admin');
    }

    public function test_kurir_is_sent_to_access_denied_page(): void
    {
        $this->actingAs(User::factory()->kurir()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('akses-ditolak'));
    }

    public function test_branch_names_are_escaped(): void
    {
        Cabang::factory()->create(['nama' => "Cabang <script>alert('xss')</script>"]);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false);
    }

    /**
     * @return array{0: Cabang, 1: Cabang}
     */
    private function duaCabang(): array
    {
        return [
            Cabang::factory()->create(['nama' => 'Cabang Uji A']),
            Cabang::factory()->create(['nama' => 'Cabang Uji B']),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function ringkasanSebagai(User $user, string $periode = 'hari_ini'): ?array
    {
        $this->actingAs($user);

        return Livewire::test('pages::dashboard')
            ->set('periode', $periode)
            ->instance()
            ->ringkasan;
    }
}
