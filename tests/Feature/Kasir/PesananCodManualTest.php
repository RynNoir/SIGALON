<?php

namespace Tests\Feature\Kasir;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\RiwayatStatus;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PesananCodManualTest extends TestCase
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

    public function test_kasir_dengan_cabang_aktif_dapat_mengakses_halaman_pesanan_cod(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($kasir)
            ->get(route('kasir.pesanan-cod'))
            ->assertOk()
            ->assertSee('Input Pesanan COD Manual')
            ->assertSee($cabang->nama)
            ->assertSee('Data Pemesan & Alamat Antar')
            ->assertSee('Layanan & Kuantitas')
            ->assertSeeHtml('data-test="preview-total-harga"')
            ->assertSeeHtml('data-test="tombol-simpan"');
    }

    public function test_menu_pesanan_cod_hanya_tampil_untuk_kasir_di_sidebar(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();
        $adminCabang = User::factory()->adminCabang()->for($cabang)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        // Kasir melihat item menu Pesanan COD
        $this->actingAs($kasir)
            ->get(route('kasir.pesanan-cod'))
            ->assertOk()
            ->assertSee('Pesanan COD');

        // Admin Cabang tidak melihat menu Pesanan COD
        $this->actingAs($adminCabang)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pesanan COD');

        // Super Admin tidak melihat menu Pesanan COD
        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pesanan COD');
    }

    // ─── 2. Otorisasi Peran & Keamanan Akses ───────────────────────────────────

    public function test_tamu_diarahkan_ke_halaman_login_saat_mengakses_pesanan_cod(): void
    {
        $this->get(route('kasir.pesanan-cod'))
            ->assertRedirect(route('login'));
    }

    public function test_super_admin_ditolak_mengakses_pesanan_cod(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)
            ->get(route('kasir.pesanan-cod'))
            ->assertForbidden();
    }

    public function test_admin_cabang_ditolak_mengakses_pesanan_cod(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $adminCabang = User::factory()->adminCabang()->for($cabang)->create();

        $this->actingAs($adminCabang)
            ->get(route('kasir.pesanan-cod'))
            ->assertForbidden();
    }

    public function test_kurir_ditolak_mengakses_pesanan_cod(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kurir = User::factory()->kurir()->for($cabang)->create();

        $this->actingAs($kurir)
            ->get(route('kasir.pesanan-cod'))
            ->assertRedirect(route('akses-ditolak'));
    }

    public function test_pelanggan_ditolak_mengakses_pesanan_cod(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $this->actingAs($pelanggan, 'pelanggan')
            ->get(route('kasir.pesanan-cod'))
            ->assertForbidden();
    }

    public function test_kasir_nonaktif_ditolak_mengakses_pesanan_cod(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create(['is_active' => false]);

        $this->actingAs($kasir)
            ->get(route('kasir.pesanan-cod'))
            ->assertRedirect(route('login'));
    }

    // ─── 3. Kasir Sukses Simpan Pesanan COD Manual ───────────────────────────

    public function test_kasir_berhasil_menyimpan_pesanan_cod_manual_tunai(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Ibu Nurul')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Merpati No. 10, Padang')
            ->set('jumlah_galon', 3)
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSeeHtml('data-test="konfirmasi-sukses"')
            ->assertSee('Pesanan #COD-')
            ->assertSee('Ibu Nurul')
            ->assertSee('081234567890')
            ->assertSee('Jl. Merpati No. 10, Padang')
            ->assertSee('3 Galon Isi Ulang')
            ->assertSee('Rp21.000');

        // Pastikan pesanan tersimpan di database persis sesuai PRD & ERD
        $this->assertDatabaseHas('transaksi', [
            'cabang_id' => $cabang->id,
            'pelanggan_id' => null, // COD manual tanpa akun pelanggan
            'kurir_id' => null, // Belum ditugaskan
            'jenis_kanal' => Kanal::Cod->value,
            'jenis_item' => JenisItem::IsiUlang->value,
            'jumlah_galon' => 3,
            'total_harga' => 21000, // 3 × 7.000
            'metode_bayar' => MetodePembayaran::Tunai->value,
            'status' => StatusTransaksi::Diproses->value,
            'nama_pemesan' => 'Ibu Nurul',
            'no_hp_pemesan' => '081234567890',
            'alamat_antar' => 'Jl. Merpati No. 10, Padang',
            'dicatat_oleh' => $kasir->id,
            'diverifikasi_oleh' => null,
        ]);

        $pesanan = Transaksi::latest('id')->first();
        $this->assertNotNull($pesanan);
        $this->assertSame('2026-11-05', $pesanan->tanggal->toDateString());

        // Pastikan riwayat status awal tercatat di tabel riwayat_status (US-10/US-12)
        $this->assertDatabaseHas('riwayat_status', [
            'transaksi_id' => $pesanan->id,
            'status_lama' => null,
            'status_baru' => StatusTransaksi::Diproses->value,
            'diubah_oleh_staff' => $kasir->id,
            'diubah_oleh_pelanggan' => null,
            'catatan' => null,
        ]);
        $this->assertCount(1, RiwayatStatus::where('transaksi_id', $pesanan->id)->get());
    }

    public function test_kasir_berhasil_menyimpan_pesanan_cod_manual_qr(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Pak Bambang')
            ->set('no_hp_pemesan', '085299887766')
            ->set('alamat_antar', 'Komplek Permata Hijau B-4')
            ->set('jumlah_galon', 2)
            ->call('pilihMetode', 'qr')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSeeHtml('data-test="konfirmasi-sukses"')
            ->assertSee('Pak Bambang')
            ->assertSee('Rp14.000');

        $this->assertDatabaseHas('transaksi', [
            'cabang_id' => $cabang->id,
            'jenis_kanal' => Kanal::Cod->value,
            'metode_bayar' => MetodePembayaran::Qr->value,
            'status' => StatusTransaksi::Diproses->value,
            'total_harga' => 14000,
        ]);
    }

    public function test_total_harga_selalu_dihitung_di_server_dan_mengabaikan_manipulasi_klien(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        // Klien mencoba mengirimkan harga palsu (misal 500 rupiah) atau cabang_id lain
        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Penyerang')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Alamat Uji Coba')
            ->set('jumlah_galon', 5)
            ->set('metode_bayar', 'tunai')
            ->set('browser_total', 500)
            ->set('browser_cabang_id', 99999)
            ->call('simpan')
            ->assertHasNoErrors();

        // Server tetap menghitung 5 × 7.000 = 35.000 dan memakai cabang milik kasir
        $this->assertDatabaseHas('transaksi', [
            'cabang_id' => $cabang->id,
            'jumlah_galon' => 5,
            'total_harga' => 35000,
        ]);
    }

    // ─── 4. Stepper & Interaktivitas UI ───────────────────────────────────────

    public function test_stepper_dan_tombol_pintasan_mengubah_kuantitas_dan_preview_total(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->assertSeeHtml('data-test="jumlah-galon-text"')
            ->assertSee('1')
            ->assertSee('Rp7.000')
            // Tambah 1 -> 2 galon (Rp14.000)
            ->call('tambahJumlah', 1)
            ->assertSee('Rp14.000')
            // Pintasan 5 galon -> Rp35.000
            ->call('setJumlah', 5)
            ->assertSee('Rp35.000')
            // Kurangi 1 -> 4 galon (Rp28.000)
            ->call('kurangiJumlah')
            ->assertSee('Rp28.000')
            // Kurangi sampai batas minimal 1 tidak pernah di bawah 1
            ->call('setJumlah', 1)
            ->call('kurangiJumlah')
            ->assertSee('Rp7.000');
    }

    public function test_tombol_input_pesanan_baru_mereset_kartu_konfirmasi(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Ibu Nurul')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Merpati No. 10, Padang')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertSeeHtml('data-test="konfirmasi-sukses"')
            ->call('inputPesananBaru')
            ->assertDontSeeHtml('data-test="konfirmasi-sukses"');
    }

    // ─── 5. Validasi Input ───────────────────────────────────────────────────

    public function test_validasi_nama_pemesan_wajib_diisi_dan_minimal_2_karakter(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', '')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Sudirman No. 1')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasErrors(['nama_pemesan' => 'required']);

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'A')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Sudirman No. 1')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasErrors(['nama_pemesan' => 'min']);
    }

    public function test_validasi_nomor_hp_pemesan_wajib_format_nomor_yang_benar(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        // Nomor kurang dari 9 digit
        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Budi')
            ->set('no_hp_pemesan', '12345')
            ->set('alamat_antar', 'Jl. Sudirman No. 1')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasErrors(['no_hp_pemesan' => 'regex']);

        // Mengandung huruf
        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Budi')
            ->set('no_hp_pemesan', '08123abcde')
            ->set('alamat_antar', 'Jl. Sudirman No. 1')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasErrors(['no_hp_pemesan' => 'regex']);
    }

    public function test_validasi_alamat_antar_wajib_diisi_dan_minimal_5_karakter(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Budi')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', '')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasErrors(['alamat_antar' => 'required']);

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Budi')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Gang')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasErrors(['alamat_antar' => 'min']);
    }

    public function test_validasi_jumlah_galon_minimal_1_dan_maksimal_500(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Budi')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Merpati No. 10')
            ->set('jumlah_galon', 0)
            ->call('simpan')
            ->assertHasErrors(['jumlah_galon' => 'min']);

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Budi')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Merpati No. 10')
            ->set('jumlah_galon', 501)
            ->call('simpan')
            ->assertHasErrors(['jumlah_galon' => 'max']);
    }

    public function test_validasi_metode_bayar_harus_tunai_atau_qr(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', 'Budi')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Merpati No. 10')
            ->set('jumlah_galon', 1)
            ->set('metode_bayar', 'transfer_bank')
            ->call('simpan')
            ->assertHasErrors(['metode_bayar']);
    }

    // ─── 6. Cabang Nonaktif / Isolasi Cabang ───────────────────────────────────

    public function test_kasir_dengan_cabang_nonaktif_tidak_dapat_menyimpan_pesanan(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => false]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->assertSeeHtml('data-test="alert-cabang-nonaktif"')
            ->set('nama_pemesan', 'Ibu Nurul')
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', 'Jl. Merpati No. 10, Padang')
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasErrors(['cabang'])
            ->assertSee('Cabang Anda sedang tidak aktif');

        $this->assertEquals(0, Transaksi::count());
    }

    // ─── 7. Escaping & Keamanan Teks Input ────────────────────────────────────

    public function test_input_nama_dan_alamat_dengan_karakter_khusus_ter_escape_dengan_aman(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => true]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $namaXSS = 'Pak Budi <script>alert("XSS")</script>';
        $alamatXSS = 'Jl. Veteran <b>No. 12</b> & "Apotek"';

        Livewire::actingAs($kasir)
            ->test('pages::kasir.pesanan-cod')
            ->set('nama_pemesan', $namaXSS)
            ->set('no_hp_pemesan', '081234567890')
            ->set('alamat_antar', $alamatXSS)
            ->set('jumlah_galon', 1)
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSeeHtml(e($namaXSS))
            ->assertDontSeeHtml('<script>alert("XSS")</script>');

        $this->assertDatabaseHas('transaksi', [
            'nama_pemesan' => $namaXSS,
            'alamat_antar' => $alamatXSS,
        ]);
    }
}
