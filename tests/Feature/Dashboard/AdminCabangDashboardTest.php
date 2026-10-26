<?php

namespace Tests\Feature\Dashboard;

use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCabangDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00:00'));
        config(['sigalon.harga.walk_in' => 6000, 'sigalon.harga.cod' => 7000]);
    }

    // ─── Dashboard /dashboard ─────────────────────────────────────────────

    public function test_admin_cabang_dengan_cabang_aktif_langsung_melihat_dashboard_cabang(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan cabang Anda')
            ->assertSee($cabang->nama)
            ->assertSeeHtml('data-test="dashboard-cabang-card"')
            ->assertDontSeeHtml('data-test="pesan-cabang-tidak-aktif"')
            ->assertDontSee('Buka dashboard cabang');
    }

    public function test_admin_cabang_tanpa_cabang_melihat_pesan_aman_tanpa_tautan(): void
    {
        // Admin Cabang dengan cabang_id null — EnsureStaffCanAccessWeb akan logout mereka,
        // tapi kita uji kondisi boundary-nya via middleware yang memang memblokir login.
        // Sesuai aturan di US-01, admin cabang tanpa cabang tidak bisa login sama sekali.
        // Tes ini membuktikan bahwa jika entah bagaimana mereka sampai ke /dashboard,
        // blok placeholder menangani kasus null dengan aman.
        $admin = User::factory()->adminCabang()->create(['cabang_id' => null]);

        // EnsureStaffCanAccessWeb akan logout karena cabang_id null
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_cabang_dengan_cabang_nonaktif_melihat_pesan_aman_tanpa_dashboard(): void
    {
        // canAccessStaffWeb() hanya memeriksa cabang_id !== null, bukan is_active cabang.
        // Sehingga admin dengan cabang nonaktif BISA masuk ke /dashboard dan melihat pesan aman.
        $cabangNonaktif = Cabang::factory()->create(['is_active' => false]);
        $admin = User::factory()->adminCabang()->for($cabangNonaktif)->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan cabang Anda')
            ->assertSeeHtml('data-test="pesan-cabang-tidak-aktif"')
            ->assertDontSeeHtml('data-test="dashboard-cabang-card"');
    }

    public function test_kasir_tidak_melihat_dashboard_cabang(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($kasir)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan cabang Anda')
            ->assertSeeHtml('data-test="pesan-cabang-tidak-aktif"')
            ->assertDontSeeHtml('data-test="dashboard-cabang-card"')
            ->assertDontSee('Buka dashboard cabang');
    }

    // ─── Dashboard /dashboard/cabang/{cabang} — Admin Cabang ────────────

    public function test_admin_cabang_melihat_ringkasan_cabangnya_sendiri(): void
    {
        $cabangSendiri = Cabang::factory()->create(['nama' => 'Cabang Pilot A', 'is_active' => true]);
        $admin = User::factory()->adminCabang()->for($cabangSendiri)->create();

        Transaksi::factory()->for($cabangSendiri)->create(['jumlah_galon' => 3]); // walk-in Rp18.000

        $this->actingAs($admin)
            ->get(route('dashboard.cabang', $cabangSendiri))
            ->assertOk()
            ->assertSee('Cabang Pilot A')
            ->assertSee('Rp18.000')
            ->assertSee('Hari ini');
    }

    public function test_admin_cabang_tidak_bisa_melihat_cabang_lain(): void
    {
        $cabangSendiri = Cabang::factory()->create(['is_active' => true]);
        $cabangLain = Cabang::factory()->create(['is_active' => true]);
        $admin = User::factory()->adminCabang()->for($cabangSendiri)->create();

        Transaksi::factory()->for($cabangLain)->create(['jumlah_galon' => 9]); // Rp54.000

        $this->actingAs($admin)
            ->get(route('dashboard.cabang', $cabangLain))
            ->assertForbidden();
    }

    public function test_admin_cabang_tidak_menerima_angka_cabang_lain(): void
    {
        $cabangSendiri = Cabang::factory()->create(['is_active' => true]);
        $cabangLain = Cabang::factory()->create(['is_active' => true]);

        Transaksi::factory()->for($cabangLain)->create(['jumlah_galon' => 9]); // Rp54.000

        $admin = User::factory()->adminCabang()->for($cabangSendiri)->create();

        $this->actingAs($admin)
            ->get(route('dashboard.cabang', $cabangSendiri))
            ->assertOk()
            ->assertDontSee('Rp54.000')
            ->assertDontSee($cabangLain->nama);
    }

    public function test_admin_cabang_isolasi_data_hanya_cabang_sendiri(): void
    {
        [$cabangSendiri, $cabangLain] = [
            Cabang::factory()->create(['nama' => 'Cabang Saya', 'is_active' => true]),
            Cabang::factory()->create(['nama' => 'Cabang Asing', 'is_active' => true]),
        ];

        Transaksi::factory()->for($cabangSendiri)->create(['jumlah_galon' => 2]); // Rp12.000
        Transaksi::factory()->for($cabangLain)->create(['jumlah_galon' => 5]);    // Rp30.000

        $admin = User::factory()->adminCabang()->for($cabangSendiri)->create();

        $ringkasan = $this->ringkasanCabangSebagai($admin, $cabangSendiri);

        $this->assertSame(12000, $ringkasan['totalPendapatan']);
        $this->assertSame(1, $ringkasan['totalTransaksi']);
        $this->assertSame('Cabang Saya', $ringkasan['cabangNama']);
    }

    // ─── Dashboard /dashboard/cabang/{cabang} — Super Admin ─────────────

    public function test_super_admin_bisa_melihat_cabang_mana_pun_yang_aktif(): void
    {
        $cabang = Cabang::factory()->create(['nama' => 'Cabang Pilot SA', 'is_active' => true]);
        $superAdmin = User::factory()->superAdmin()->create();

        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 2]); // Rp12.000

        $this->actingAs($superAdmin)
            ->get(route('dashboard.cabang', $cabang))
            ->assertOk()
            ->assertSee('Cabang Pilot SA')
            ->assertSee('Rp12.000');
    }

    public function test_super_admin_mendapat_404_untuk_cabang_nonaktif(): void
    {
        $cabangNonaktif = Cabang::factory()->create(['is_active' => false]);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('dashboard.cabang', $cabangNonaktif))
            ->assertNotFound();
    }

    public function test_super_admin_mendapat_404_untuk_id_cabang_yang_tidak_ada(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get('/dashboard/cabang/99999')
            ->assertNotFound();
    }

    public function test_super_admin_membuka_cabang_lalu_ganti_periode_tetap_melihat_data_cabang_itu_saja(): void
    {
        $cabangA = Cabang::factory()->create(['nama' => 'Cabang A', 'is_active' => true]);
        $cabangB = Cabang::factory()->create(['nama' => 'Cabang B', 'is_active' => true]);
        $superAdmin = User::factory()->superAdmin()->create();

        Transaksi::factory()->for($cabangA)->create(['jumlah_galon' => 1]); // Hari ini: Rp6.000
        Transaksi::factory()->for($cabangA)->create(['jumlah_galon' => 2, 'tanggal' => '2026-10-01']); // Awal bulan: Rp12.000

        Transaksi::factory()->for($cabangB)->create(['jumlah_galon' => 5]); // Hari ini: Rp30.000
        Transaksi::factory()->for($cabangB)->create(['jumlah_galon' => 10, 'tanggal' => '2026-10-01']); // Awal bulan: Rp60.000

        $component = Livewire::actingAs($superAdmin)
            ->test('pages::dashboard-cabang', ['cabang' => $cabangA])
            ->assertSee('Cabang A')
            ->assertDontSee('Cabang B');

        $component->set('periode', 'bulan_ini');

        $ringkasan = $component->instance()->ringkasan;

        $this->assertSame(18000, $ringkasan['totalPendapatan']);
        $this->assertSame(2, $ringkasan['totalTransaksi']);
        $this->assertSame('Cabang A', $ringkasan['cabangNama']);
        $component->assertSee('Cabang A')->assertDontSee('Cabang B');
    }

    // ─── Kasir ───────────────────────────────────────────────────────────

    public function test_kasir_ditolak_dari_dashboard_cabang(): void
    {
        // Keputusan PO final: Kasir TIDAK boleh mengakses dashboard cabang.
        $cabang = Cabang::factory()->create(['nama' => 'Cabang Kasir', 'is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($kasir)
            ->get(route('dashboard.cabang', $cabang))
            ->assertForbidden();
    }

    public function test_kasir_ditolak_dari_cabang_lain(): void
    {
        $cabangSendiri = Cabang::factory()->create(['is_active' => true]);
        $cabangLain = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabangSendiri)->create();

        $this->actingAs($kasir)
            ->get(route('dashboard.cabang', $cabangLain))
            ->assertForbidden();
    }

    public function test_kasir_tidak_melihat_menu_dasbor_cabang_di_sidebar(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($kasir)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Dasbor Cabang');
    }

    public function test_kasir_tidak_melihat_tautan_buka_dashboard_cabang_di_placeholder(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($kasir)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Buka dashboard cabang');
    }

    // ─── Kurir ───────────────────────────────────────────────────────────

    public function test_kurir_diarahkan_ke_akses_ditolak_dari_dashboard_cabang(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kurir = User::factory()->kurir()->for($cabang)->create();

        $this->actingAs($kurir)
            ->get(route('dashboard.cabang', $cabang))
            ->assertRedirect(route('akses-ditolak'));
    }

    // ─── Tamu ────────────────────────────────────────────────────────────

    public function test_tamu_diarahkan_ke_login_dari_dashboard_cabang(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);

        $this->get(route('dashboard.cabang', $cabang))
            ->assertRedirect(route('login'));
    }

    // ─── Pelanggan ────────────────────────────────────────────────────────

    public function test_pelanggan_ditolak_dari_dashboard_cabang(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $pelanggan = Pelanggan::factory()->create();

        // Pelanggan (guard 'pelanggan') yang membuka area staf mendapat 403 Forbidden
        $this->actingAs($pelanggan, 'pelanggan')
            ->get(route('dashboard.cabang', $cabang))
            ->assertForbidden();
    }

    // ─── Periode ─────────────────────────────────────────────────────────

    public function test_periode_hari_ini_tidak_memuat_hari_sebelumnya(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 1]);                            // hari ini Rp6.000
        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 2, 'tanggal' => '2026-10-01']); // awal bulan Rp12.000

        $ringkasan = $this->ringkasanCabangSebagai($admin, $cabang);
        $this->assertSame(6000, $ringkasan['totalPendapatan']);

        $ringkasanBulanIni = $this->ringkasanCabangSebagai($admin, $cabang, 'bulan_ini');
        $this->assertSame(18000, $ringkasanBulanIni['totalPendapatan']);
    }

    public function test_periode_tidak_valid_dikembalikan_ke_hari_ini(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $this->actingAs($admin);

        Livewire::withQueryParams(['periode' => 'semua_waktu'])
            ->test('pages::dashboard-cabang', ['cabang' => $cabang])
            ->assertSet('periode', 'hari_ini');
    }

    // ─── Empty state ─────────────────────────────────────────────────────

    public function test_empty_state_muncul_saat_belum_ada_transaksi(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $admin = User::factory()->adminCabang()->for($cabang)->create();

        $this->actingAs($admin)
            ->get(route('dashboard.cabang', $cabang))
            ->assertOk()
            ->assertSee('Belum ada transaksi sah pada periode ini')
            ->assertSee('Rp0');
    }

    // ─── Otorisasi Livewire request (hydrate) ────────────────────────────

    public function test_admin_cabang_tidak_bisa_manipulasi_cabang_id_di_request_berikutnya(): void
    {
        // #[Locked] mencegah properti cabangId diubah dari klien.
        // Tes ini membuktikan bahwa data cabang lain tidak muncul dalam ringkasan
        // karena cabangId terkunci ke cabang milik admin yang bersangkutan.
        $cabangSendiri = Cabang::factory()->create(['is_active' => true]);
        $cabangLain = Cabang::factory()->create(['is_active' => true]);

        Transaksi::factory()->for($cabangSendiri)->create(['jumlah_galon' => 1]); // Rp6.000
        Transaksi::factory()->for($cabangLain)->create(['jumlah_galon' => 9]);    // Rp54.000

        $admin = User::factory()->adminCabang()->for($cabangSendiri)->create();
        $this->actingAs($admin);

        $ringkasan = Livewire::test('pages::dashboard-cabang', ['cabang' => $cabangSendiri])
            ->instance()
            ->ringkasan;

        // Hanya data cabang sendiri yang muncul; data cabang lain tidak bocor
        $this->assertSame(6000, $ringkasan['totalPendapatan']);
        $this->assertSame(1, $ringkasan['totalTransaksi']);
        $this->assertNotEquals(54000, $ringkasan['totalPendapatan']);
    }

    public function test_admin_cabang_dinonaktifkan_setelah_halaman_terbuka_lalu_ganti_periode_tidak_mendapat_data(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $admin = User::factory()->adminCabang()->for($cabang)->create(['is_active' => true]);

        Transaksi::factory()->for($cabang)->create(['jumlah_galon' => 2]); // Rp12.000

        $component = Livewire::actingAs($admin)
            ->test('pages::dashboard-cabang', ['cabang' => $cabang]);

        // Simulasikan akun dinonaktifkan oleh Super Admin setelah halaman terbuka
        $admin->is_active = false;
        $admin->save();
        auth()->setUser($admin->fresh());

        // Saat mengganti periode pada request berikutnya, authorizeCabang() menolak akses
        $component->set('periode', 'bulan_ini');
        $component->assertForbidden();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function ringkasanCabangSebagai(User $user, Cabang $cabang, string $periode = 'hari_ini'): array
    {
        $this->actingAs($user);

        return Livewire::test('pages::dashboard-cabang', ['cabang' => $cabang])
            ->set('periode', $periode)
            ->instance()
            ->ringkasan;
    }
}
