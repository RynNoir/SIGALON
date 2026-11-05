<?php

namespace Tests\Feature\Kasir;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransaksiWalkInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-05 10:00:00'));
        config([
            'sigalon.harga.walk_in' => 6000,
            'sigalon.harga.galon_baru' => 45000,
            'sigalon.harga.cod' => 7000,
        ]);
    }

    // ─── 1. Akses Halaman & Sidebar ──────────────────────────────────────────

    public function test_kasir_dengan_cabang_aktif_dapat_mengakses_halaman_transaksi_walkin(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($kasir)
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('Input Transaksi Walk-in & Galon Baru')
            ->assertSee($cabang->nama)
            ->assertSee('Air Isi Ulang')
            ->assertSee('Galon Kosong Baru')
            ->assertSeeHtml('data-test="total-harga"')
            ->assertSeeHtml('data-test="btn-simpan-transaksi"');
    }

    public function test_menu_transaksi_walkin_hanya_tampil_untuk_kasir_di_sidebar(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();
        $adminCabang = User::factory()->adminCabang()->for($cabang)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        // Kasir melihat item menu Transaksi Walk-in
        $this->actingAs($kasir)
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSee('Transaksi Walk-in');

        // Admin Cabang tidak melihat menu Transaksi Walk-in
        $this->actingAs($adminCabang)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Transaksi Walk-in');

        // Super Admin tidak melihat menu Transaksi Walk-in
        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Transaksi Walk-in');
    }

    // ─── 2. Kasir Sukses Simpan Transaksi Walk-in ─────────────────────────────

    public function test_kasir_berhasil_input_transaksi_isi_ulang_tunai_dengan_field_otomatis_dan_total_dari_config(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jenis_item', JenisItem::IsiUlang->value)
            ->set('jumlah_galon', 4)
            ->set('metode_bayar', MetodePembayaran::Tunai->value)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSeeHtml('data-test="konfirmasi-transaksi"')
            ->assertSeeHtml('data-test="konfirmasi-total"')
            ->assertSee('Rp24.000'); // 4 * 6000 = 24.000

        $transaksi = Transaksi::first();
        $this->assertNotNull($transaksi);
        $this->assertSame($cabang->id, $transaksi->cabang_id);
        $this->assertNull($transaksi->pelanggan_id);
        $this->assertNull($transaksi->kurir_id);
        $this->assertSame(Kanal::WalkIn, $transaksi->jenis_kanal);
        $this->assertSame(JenisItem::IsiUlang, $transaksi->jenis_item);
        $this->assertSame(4, $transaksi->jumlah_galon);
        $this->assertSame(24000, $transaksi->total_harga);
        $this->assertSame(MetodePembayaran::Tunai, $transaksi->metode_bayar);
        $this->assertSame(StatusTransaksi::Selesai, $transaksi->status);
        $this->assertSame('2026-11-05', $transaksi->tanggal->toDateString());
        $this->assertNull($transaksi->nama_pemesan);
        $this->assertNull($transaksi->no_hp_pemesan);
        $this->assertNull($transaksi->alamat_antar);
        $this->assertSame($kasir->id, $transaksi->dicatat_oleh);
        $this->assertNull($transaksi->diverifikasi_oleh);
    }

    public function test_kasir_berhasil_input_transaksi_galon_baru_qr_dengan_total_dari_config(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->call('pilihItem', JenisItem::GalonBaru->value)
            ->set('jumlah_galon', 2)
            ->call('pilihMetode', MetodePembayaran::Qr->value)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSeeHtml('data-test="konfirmasi-transaksi"')
            ->assertSee('Rp90.000'); // 2 * 45000 = 90.000

        $transaksi = Transaksi::first();
        $this->assertNotNull($transaksi);
        $this->assertSame($cabang->id, $transaksi->cabang_id);
        $this->assertNull($transaksi->pelanggan_id);
        $this->assertSame(Kanal::WalkIn, $transaksi->jenis_kanal);
        $this->assertSame(JenisItem::GalonBaru, $transaksi->jenis_item);
        $this->assertSame(2, $transaksi->jumlah_galon);
        $this->assertSame(90000, $transaksi->total_harga);
        $this->assertSame(MetodePembayaran::Qr, $transaksi->metode_bayar);
        $this->assertSame(StatusTransaksi::Selesai, $transaksi->status);
        $this->assertSame($kasir->id, $transaksi->dicatat_oleh);
    }

    public function test_konfirmasi_menampilkan_total_dan_form_kembali_kosong_setelah_tersimpan(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $component = Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->call('pilihItem', JenisItem::GalonBaru->value)
            ->set('jumlah_galon', 3)
            ->call('pilihMetode', MetodePembayaran::Qr->value)
            ->call('simpan')
            ->assertHasNoErrors();

        // Konfirmasi tampil dengan total
        $component->assertSeeHtml('data-test="konfirmasi-transaksi"')
            ->assertSee('Rp135.000'); // 3 * 45.000

        // Form kembali ke default (kosong/reset)
        $this->assertSame(JenisItem::IsiUlang->value, $component->get('jenis_item'));
        $this->assertSame(1, $component->get('jumlah_galon'));
        $this->assertSame(MetodePembayaran::Tunai->value, $component->get('metode_bayar'));
    }

    // ─── 3. Integritas Server: Total & Cabang dari Browser Diabaikan ─────────

    public function test_total_dari_browser_diabaikan_dan_selalu_dihitung_di_server_dari_config(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        // Coba manipulasi total melalui browser_total = 1000
        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jenis_item', JenisItem::IsiUlang->value)
            ->set('jumlah_galon', 5) // 5 * 6000 = 30.000
            ->set('metode_bayar', MetodePembayaran::Tunai->value)
            ->set('browser_total', 1000) // Nilai manipulasi
            ->call('simpan')
            ->assertHasNoErrors();

        $transaksi = Transaksi::first();
        $this->assertNotNull($transaksi);
        // Total harus 30.000 (dari config), bukan 1000 dari browser
        $this->assertSame(30000, $transaksi->total_harga);
    }

    public function test_cabang_id_dari_input_browser_diabaikan_dan_selalu_memakai_cabang_kasir(): void
    {
        $cabangKasir = Cabang::factory()->create(['is_active' => true]);
        $cabangLain = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabangKasir)->create();

        // Coba manipulasi cabang melalui browser_cabang_id
        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jenis_item', JenisItem::IsiUlang->value)
            ->set('jumlah_galon', 2)
            ->set('metode_bayar', MetodePembayaran::Tunai->value)
            ->set('browser_cabang_id', $cabangLain->id)
            ->call('simpan')
            ->assertHasNoErrors();

        $transaksi = Transaksi::first();
        $this->assertNotNull($transaksi);
        // Cabang wajib cabang kasir yang login, bukan cabang lain
        $this->assertSame($cabangKasir->id, $transaksi->cabang_id);
    }

    // ─── 4. Validasi Input ───────────────────────────────────────────────────

    public function test_validasi_menolak_jenis_item_yang_tidak_sah(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jenis_item', 'kopi_galon')
            ->call('simpan')
            ->assertHasErrors(['jenis_item']);

        $this->assertSame(0, Transaksi::count());
    }

    public function test_validasi_menolak_metode_bayar_yang_tidak_sah(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('metode_bayar', 'paylater')
            ->call('simpan')
            ->assertHasErrors(['metode_bayar']);

        $this->assertSame(0, Transaksi::count());
    }

    public function test_validasi_menolak_jumlah_galon_nol_atau_negatif(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jumlah_galon', 0)
            ->call('simpan')
            ->assertHasErrors(['jumlah_galon']);

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jumlah_galon', -5)
            ->call('simpan')
            ->assertHasErrors(['jumlah_galon']);

        $this->assertSame(0, Transaksi::count());
    }

    public function test_validasi_menolak_jumlah_galon_melebihi_batas_atas(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jumlah_galon', 501)
            ->call('simpan')
            ->assertHasErrors(['jumlah_galon']);

        $this->assertSame(0, Transaksi::count());
    }

    // ─── 5. Otorisasi Kasir & Cabang ─────────────────────────────────────────

    public function test_kasir_tanpa_cabang_tidak_bisa_menyimpan_transaksi_dengan_pesan_aman(): void
    {
        // Kasir dengan cabang_id null
        $kasir = User::factory()->kasir()->create(['cabang_id' => null]);

        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jumlah_galon', 2)
            ->call('simpan')
            ->assertHasErrors(['cabang'])
            ->assertSeeHtml('data-test="error-cabang"');

        $this->assertSame(0, Transaksi::count());
    }

    public function test_kasir_dengan_cabang_nonaktif_tidak_bisa_menyimpan_transaksi_dengan_pesan_aman(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => false]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        // Halaman memunculkan banner peringatan cabang nonaktif dan tombol simpan disabled
        $this->actingAs($kasir)
            ->get(route('kasir.transaksi'))
            ->assertOk()
            ->assertSeeHtml('data-test="pesan-cabang-nonaktif"');

        // Pemanggilan simpan langsung melalui Livewire menghasilkan pesan aman, bukan error 500
        Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->set('jumlah_galon', 3)
            ->call('simpan')
            ->assertHasErrors(['cabang'])
            ->assertSeeHtml('data-test="error-cabang"');

        $this->assertSame(0, Transaksi::count());
    }

    public function test_super_admin_dan_admin_cabang_ditolak_dari_halaman_input_transaksi(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $superAdmin = User::factory()->superAdmin()->create();
        $adminCabang = User::factory()->adminCabang()->for($cabang)->create();

        // Super Admin ditolak (403)
        $this->actingAs($superAdmin)
            ->get(route('kasir.transaksi'))
            ->assertForbidden();

        // Admin Cabang ditolak (403)
        $this->actingAs($adminCabang)
            ->get(route('kasir.transaksi'))
            ->assertForbidden();
    }

    public function test_kurir_ditolak_dari_halaman_input_transaksi(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kurir = User::factory()->kurir()->for($cabang)->create();

        // Kurir dikeluarkan ke akses-ditolak oleh middleware EnsureStaffCanAccessWeb
        $this->actingAs($kurir)
            ->get(route('kasir.transaksi'))
            ->assertRedirect(route('akses-ditolak'));
    }

    public function test_pelanggan_ditolak_dari_halaman_input_transaksi(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        // Pelanggan dengan guard pelanggan membuka rute staf mendapat 403
        $this->actingAs($pelanggan, 'pelanggan')
            ->get(route('kasir.transaksi'))
            ->assertForbidden();
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get(route('kasir.transaksi'))
            ->assertRedirect(route('login'));
    }

    public function test_otorisasi_diulang_di_setiap_request_livewire(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $component = Livewire::actingAs($kasir)
            ->test('pages::kasir.transaksi')
            ->assertOk();

        // Ubah role pengguna menjadi Admin Cabang di tengah sesi
        $kasir->role = Role::AdminCabang;
        $kasir->save();
        auth()->setUser($kasir->fresh());

        // Request Livewire berikutnya (misal panggil pilihItem) langsung menolak akses (403)
        $component->call('pilihItem', JenisItem::GalonBaru->value);
        $component->assertForbidden();
    }
}
