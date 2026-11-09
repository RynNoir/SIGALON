<?php

namespace Tests\Feature\Cabang;

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
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * US-15: Daftar transaksi cabang dengan filter.
 *
 * Sprint 2 — Kasir dan Admin Cabang melihat transaksi cabangnya sendiri,
 * dapat difilter per kanal, status, dan tanggal, dengan pagination.
 */
class DaftarTransaksiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function cabangAktif(string $nama = 'Cabang Padang Barat'): Cabang
    {
        return Cabang::factory()->create([
            'nama' => $nama,
            'is_active' => true,
        ]);
    }

    private function cabangNonaktif(): Cabang
    {
        return Cabang::factory()->inactive()->create([
            'nama' => 'Cabang Nonaktif',
        ]);
    }

    private function kasir(Cabang $cabang): User
    {
        return User::factory()->create([
            'role' => Role::Kasir,
            'cabang_id' => $cabang->id,
            'is_active' => true,
        ]);
    }

    private function adminCabang(Cabang $cabang): User
    {
        return User::factory()->create([
            'role' => Role::AdminCabang,
            'cabang_id' => $cabang->id,
            'is_active' => true,
        ]);
    }

    private function buatTransaksi(
        Cabang $cabang,
        Kanal $kanal = Kanal::WalkIn,
        StatusTransaksi $status = StatusTransaksi::Selesai,
        ?string $tanggal = null,
        int $total = 6000,
        ?User $pencatat = null,
        ?Pelanggan $pelanggan = null,
        ?string $namaPemesan = null
    ): Transaksi {
        $trx = new Transaksi;
        $trx->cabang_id = $cabang->id;
        $trx->pelanggan_id = $pelanggan?->id;
        $trx->kurir_id = null;
        $trx->jenis_kanal = $kanal;
        $trx->jenis_item = JenisItem::IsiUlang;
        $trx->jumlah_galon = 1;
        $trx->total_harga = $total;
        $trx->metode_bayar = MetodePembayaran::Tunai;
        $trx->status = $status;
        $trx->tanggal = $tanggal !== null ? CarbonImmutable::parse($tanggal) : CarbonImmutable::today();
        $trx->nama_pemesan = $namaPemesan;
        $trx->no_hp_pemesan = null;
        $trx->alamat_antar = null;
        $trx->dicatat_oleh = $pencatat?->id;
        $trx->diverifikasi_oleh = null;
        $trx->save();

        return $trx;
    }

    // -------------------------------------------------------------------------
    // 1. Otorisasi Role & Akses Halaman
    // -------------------------------------------------------------------------

    public function test_kasir_dapat_mengakses_halaman_transaksi_cabang(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $this->actingAs($kasir)
            ->get(route('cabang.transaksi'))
            ->assertOk()
            ->assertSee(__('Daftar Transaksi Cabang'));
    }

    public function test_admin_cabang_dapat_mengakses_halaman_transaksi_cabang(): void
    {
        $cabang = $this->cabangAktif();
        $admin = $this->adminCabang($cabang);

        $this->actingAs($admin)
            ->get(route('cabang.transaksi'))
            ->assertOk()
            ->assertSee(__('Daftar Transaksi Cabang'));
    }

    public function test_super_admin_ditolak_dari_halaman_transaksi_cabang(): void
    {
        $superAdmin = User::factory()->create([
            'role' => Role::SuperAdmin,
            'cabang_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('cabang.transaksi'))
            ->assertForbidden();
    }

    public function test_kurir_ditolak_dari_halaman_transaksi_cabang(): void
    {
        $cabang = $this->cabangAktif();
        $kurir = User::factory()->create([
            'role' => Role::Kurir,
            'cabang_id' => $cabang->id,
            'is_active' => true,
        ]);

        // Kurir diarahkan ke halaman akses ditolak via EnsureStaffCanAccessWeb
        $this->actingAs($kurir)
            ->get(route('cabang.transaksi'))
            ->assertRedirect(route('akses-ditolak'));
    }

    public function test_pelanggan_ditolak_dari_halaman_transaksi_cabang(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        // Guard pelanggan diarahkan 403 saat membuka area staf
        $this->actingAs($pelanggan, 'pelanggan')
            ->get(route('cabang.transaksi'))
            ->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_login_staf(): void
    {
        $this->get(route('cabang.transaksi'))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_atau_admin_cabang_tanpa_cabang_atau_cabang_nonaktif_melihat_peringatan_aman(): void
    {
        $cabangNonaktif = $this->cabangNonaktif();
        $kasir = $this->kasir($cabangNonaktif);

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->assertSeeHtml('data-test="alert-cabang-nonaktif"')
            ->assertDontSeeHtml('data-test="panel-filter"');
    }

    // -------------------------------------------------------------------------
    // 2. Menu Sidebar (Syarat Verifikasi 5a)
    // -------------------------------------------------------------------------

    public function test_menu_transaksi_tampil_di_sidebar_untuk_kasir_dan_admin_cabang_dan_tidak_tampil_untuk_peran_lain(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);
        $admin = $this->adminCabang($cabang);
        $superAdmin = User::factory()->create(['role' => Role::SuperAdmin, 'cabang_id' => null]);

        // Kasir melihat menu Transaksi
        $this->actingAs($kasir)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('cabang.transaksi'));

        // Admin Cabang melihat menu Transaksi
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('cabang.transaksi'));

        // Super Admin TIDAK melihat menu Transaksi cabang
        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('cabang.transaksi'));
    }

    // -------------------------------------------------------------------------
    // 3. Kasir & Admin Cabang di Cabang Sama Melihat Data Sama (Syarat Verifikasi 5b)
    // -------------------------------------------------------------------------

    public function test_kasir_dan_admin_cabang_di_cabang_yang_sama_melihat_data_yang_sama(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);
        $admin = $this->adminCabang($cabang);

        $trx1 = $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai, '2026-11-09', 6000, $kasir);
        $trx2 = $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Diproses, '2026-11-09', 7000);

        // Kasir melihat kedua transaksi
        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->assertSee('#TRX-'.$trx1->id)
            ->assertSee('#TRX-'.$trx2->id);

        // Admin Cabang melihat data yang sama persis
        Livewire::actingAs($admin)
            ->test('pages::cabang.transaksi')
            ->assertSee('#TRX-'.$trx1->id)
            ->assertSee('#TRX-'.$trx2->id);
    }

    // -------------------------------------------------------------------------
    // 4. Isolasi Ketat Antar Cabang
    // -------------------------------------------------------------------------

    public function test_isolasi_ketat_staf_cabang_a_tidak_pernah_melihat_transaksi_cabang_b_meski_parameter_dimanipulasi(): void
    {
        $cabangA = $this->cabangAktif('Cabang A');
        $cabangB = $this->cabangAktif('Cabang B');

        $kasirA = $this->kasir($cabangA);
        $trxA = $this->buatTransaksi($cabangA, Kanal::WalkIn, StatusTransaksi::Selesai, null, 12000);
        $trxB = $this->buatTransaksi($cabangB, Kanal::WalkIn, StatusTransaksi::Selesai, null, 18000);

        Livewire::actingAs($kasirA)
            ->test('pages::cabang.transaksi', ['cabang_id' => $cabangB->id]) // Upaya manipulasi parameter
            ->assertSee('#TRX-'.$trxA->id)
            ->assertDontSee('#TRX-'.$trxB->id)
            ->assertDontSee('Rp18.000');
    }

    // -------------------------------------------------------------------------
    // 5. Transaksi Dibatalkan dan Semua Kanal Tetap Tampil (Syarat Verifikasi 5c)
    // -------------------------------------------------------------------------

    public function test_menampilkan_semua_kanal_dan_transaksi_dibatalkan_tetap_tampil(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $trxWalkIn = $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai);
        $trxCodBatal = $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Dibatalkan);
        $trxCanvassing = $this->buatTransaksi($cabang, Kanal::Canvassing, StatusTransaksi::MenungguVerifikasi);

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->assertSee('#TRX-'.$trxWalkIn->id)
            ->assertSee('#TRX-'.$trxCodBatal->id)
            ->assertSee(StatusTransaksi::Dibatalkan->label())
            ->assertSee('#TRX-'.$trxCanvassing->id);
    }

    // -------------------------------------------------------------------------
    // 6. Filter Kanal, Status, dan Tanggal
    // -------------------------------------------------------------------------

    public function test_filter_berdasarkan_kanal(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $trxWalkIn = $this->buatTransaksi($cabang, Kanal::WalkIn);
        $trxCod = $this->buatTransaksi($cabang, Kanal::Cod);

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('kanal', 'walk_in')
            ->assertSee('#TRX-'.$trxWalkIn->id)
            ->assertDontSee('#TRX-'.$trxCod->id);
    }

    public function test_filter_berdasarkan_status(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $trxSelesai = $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai);
        $trxDiproses = $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Diproses);

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('status', 'diproses')
            ->assertSee('#TRX-'.$trxDiproses->id)
            ->assertDontSee('#TRX-'.$trxSelesai->id);
    }

    public function test_filter_berdasarkan_rentang_tanggal(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $trxKemarin = $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai, '2026-11-01');
        $trxTarget = $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai, '2026-11-05');
        $trxBesok = $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai, '2026-11-09');

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('dari_tanggal', '2026-11-04')
            ->set('sampai_tanggal', '2026-11-06')
            ->assertSee('#TRX-'.$trxTarget->id)
            ->assertDontSee('#TRX-'.$trxKemarin->id)
            ->assertDontSee('#TRX-'.$trxBesok->id);
    }

    public function test_filter_kombinasi_kanal_status_dan_tanggal(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $target = $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Diproses, '2026-11-05');
        $bedaKanal = $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Diproses, '2026-11-05');
        $bedaStatus = $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Selesai, '2026-11-05');
        $bedaTanggal = $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Diproses, '2026-11-01');

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('kanal', 'cod')
            ->set('status', 'diproses')
            ->set('dari_tanggal', '2026-11-04')
            ->set('sampai_tanggal', '2026-11-06')
            ->assertSee('#TRX-'.$target->id)
            ->assertDontSee('#TRX-'.$bedaKanal->id)
            ->assertDontSee('#TRX-'.$bedaStatus->id)
            ->assertDontSee('#TRX-'.$bedaTanggal->id);
    }

    public function test_validasi_menolak_tanggal_dari_lebih_besar_dari_sampai(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai, '2026-11-05');

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('dari_tanggal', '2026-11-10')
            ->set('sampai_tanggal', '2026-11-05')
            ->assertHasErrors(['tanggal'])
            ->assertSeeHtml('data-test="error-tanggal"');
    }

    public function test_nilai_filter_kanal_dan_status_tidak_sah_diabaikan_secara_aman(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $trx = $this->buatTransaksi($cabang);

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('kanal', 'kanal_ilegal')
            ->set('status', 'status_palsu')
            ->assertHasNoErrors()
            ->assertSee('#TRX-'.$trx->id); // Tetap aman menampilkan data cabang
    }

    public function test_tombol_reset_filter_mengembalikan_semua_transaksi(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        $trxWalkIn = $this->buatTransaksi($cabang, Kanal::WalkIn);
        $trxCod = $this->buatTransaksi($cabang, Kanal::Cod);

        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('kanal', 'walk_in')
            ->assertSee('#TRX-'.$trxWalkIn->id)
            ->assertDontSee('#TRX-'.$trxCod->id)
            ->call('resetFilter')
            ->assertSet('kanal', '')
            ->assertSet('status', '')
            ->assertSet('dari_tanggal', '')
            ->assertSet('sampai_tanggal', '')
            ->assertSee('#TRX-'.$trxWalkIn->id)
            ->assertSee('#TRX-'.$trxCod->id);
    }

    // -------------------------------------------------------------------------
    // 7. Pagination 15 per Halaman & Pertahankan Filter
    // -------------------------------------------------------------------------

    public function test_pagination_15_per_halaman_dan_filter_dipertahankan(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        // Buat 16 transaksi walk-in
        for ($i = 1; $i <= 16; $i++) {
            $this->buatTransaksi($cabang, Kanal::WalkIn, StatusTransaksi::Selesai, '2026-11-01');
        }

        // Halaman 1 memuat 15 item
        $test = Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->assertSeeHtml('data-test="pagination-wrapper"');

        $this->assertCount(15, $test->get('transaksiList')->items());

        // Navigasi ke halaman 2 memuat 1 item sisa
        $test->call('setPage', 2);
        $this->assertCount(1, $test->get('transaksiList')->items());

        // Mengubah filter mereset halaman kembali ke 1
        $test->set('kanal', 'walk_in');
        $this->assertEquals(1, $test->get('transaksiList')->currentPage());
    }

    // -------------------------------------------------------------------------
    // 8. Empty States
    // -------------------------------------------------------------------------

    public function test_empty_state_saat_belum_ada_transaksi_dan_saat_filter_tidak_ada_hasil(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);

        // Belum ada transaksi
        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->assertSeeHtml('data-test="empty-state"');

        // Buat satu transaksi
        $this->buatTransaksi($cabang, Kanal::WalkIn);

        // Filter yang tidak cocok
        Livewire::actingAs($kasir)
            ->test('pages::cabang.transaksi')
            ->set('kanal', 'canvassing')
            ->assertSeeHtml('data-test="empty-filter"');
    }

    // -------------------------------------------------------------------------
    // 9. Escaping XSS
    // -------------------------------------------------------------------------

    public function test_escaping_mencegah_xss_pada_data_pemesan(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);
        $payloadXss = '<script>alert("hack")</script>';

        $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Diproses, null, 7000, null, null, $payloadXss);

        $response = $this->actingAs($kasir)->get(route('cabang.transaksi'));
        $response->assertOk();
        // Memastikan tag script mentah tidak ada di DOM HTML
        $response->assertDontSee($payloadXss, false);
    }

    // -------------------------------------------------------------------------
    // 10. Bebas N+1 Query (Syarat Verifikasi 5d)
    // -------------------------------------------------------------------------

    public function test_tidak_ada_n_plus_one_query_pada_daftar_transaksi(): void
    {
        $cabang = $this->cabangAktif();
        $kasir = $this->kasir($cabang);
        $pelanggan = Pelanggan::factory()->create();

        // 1 transaksi pertama
        $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Diproses, null, 7000, $kasir, $pelanggan);

        // Warm up component agar cache model/user/cabang sudah terisi
        Livewire::actingAs($kasir)->test('pages::cabang.transaksi');

        // Ukur query pada 1 transaksi
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($kasir)->test('pages::cabang.transaksi');

        $queryCount1 = count(DB::getQueryLog());

        // Tambah 10 transaksi lagi dengan relasi pencatat dan pelanggan
        for ($i = 1; $i <= 10; $i++) {
            $this->buatTransaksi($cabang, Kanal::Cod, StatusTransaksi::Diproses, null, 7000, $kasir, $pelanggan);
        }

        // Ukur query pada 11 transaksi
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($kasir)->test('pages::cabang.transaksi');

        $queryCount2 = count(DB::getQueryLog());

        // Jumlah query harus tetap sama (konstan) meski data bertambah 10x lipat
        $this->assertEquals($queryCount1, $queryCount2, 'Query count must remain constant regardless of row count (No N+1).');
    }
}
