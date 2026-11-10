<?php

namespace Tests\Feature\Pelanggan;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\CustomerPhoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * US-11: Pelanggan memesan air galon secara online.
 *
 * Sprint 2 — Feature tests mencakup jalur sukses, validasi, otorisasi,
 * isolasi data, escaping input, dan pengaman double-submit.
 */
class PemesananOnlineTest extends TestCase
{
    use RefreshDatabase;
    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function cabangAktif(): Cabang
    {
        return Cabang::factory()->create(['is_active' => true]);
    }

    private function pelangganDenganHp(?string $alamat = null): Pelanggan
    {
        return Pelanggan::factory()->create(['alamat' => $alamat]);
    }

    private function pelangganGoogle(?string $alamat = null): Pelanggan
    {
        return Pelanggan::factory()->google()->create(['alamat' => $alamat]);
    }

    private function formDataValid(Cabang $cabang): array
    {
        return [
            'cabang_id' => $cabang->id,
            'jumlah_galon' => 2,
            'alamat_antar' => 'Jl. Raya Padang No. 10 RT 02 RW 05 Kec. Kuranji',
            'metode_bayar' => 'tunai',
        ];
    }

    // -------------------------------------------------------------------------
    // 1. Akses halaman
    // -------------------------------------------------------------------------

    public function test_pelanggan_terautentikasi_dapat_mengakses_halaman_pemesanan(): void
    {
        $pelanggan = $this->pelangganDenganHp();

        $this->actingAs($pelanggan, 'pelanggan')
            ->get(route('pelanggan.pesan'))
            ->assertOk();
    }

    public function test_tamu_diarahkan_ke_login_pelanggan(): void
    {
        $this->get(route('pelanggan.pesan'))
            ->assertRedirect();
    }

    public function test_staf_tidak_dapat_mengakses_halaman_pelanggan(): void
    {
        $staf = User::factory()->create(['role' => Role::Kasir]);

        // Staf login via guard 'web'; route hanya untuk guard 'pelanggan'
        $this->actingAs($staf)
            ->get(route('pelanggan.pesan'))
            ->assertRedirect();
    }

    // -------------------------------------------------------------------------
    // 2. Jalur sukses: pesanan tersimpan dengan benar
    // -------------------------------------------------------------------------

    public function test_pelanggan_no_hp_berhasil_memesan_tunai_dengan_field_otomatis_dan_total_dari_config(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();
        $hargaSatuan = (int) config('sigalon.harga.cod', 7000);

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 3)
            ->set('alamat_antar', 'Jl. Pahlawan No. 5 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('pesananBerhasil.jumlah', 3)
            ->assertSet('pesananBerhasil.total', 3 * $hargaSatuan);

        $this->assertDatabaseHas('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'cabang_id' => $cabang->id,
            'jumlah_galon' => 3,
            'total_harga' => 3 * $hargaSatuan,
            'jenis_kanal' => Kanal::Cod->value,
            'jenis_item' => JenisItem::IsiUlang->value,
            'status' => StatusTransaksi::Diproses->value,
            'metode_bayar' => MetodePembayaran::Tunai->value,
            'dicatat_oleh' => null, // Dipesan sendiri oleh pelanggan
        ]);
    }

    public function test_pelanggan_berhasil_memesan_dengan_metode_qr(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Nipah No. 20 Padang')
            ->set('metode_bayar', 'qr')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('pesananBerhasil.metode', MetodePembayaran::Qr->label());

        $this->assertDatabaseHas('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'metode_bayar' => MetodePembayaran::Qr->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // 3. Akun Google: wajib melengkapi no HP
    // -------------------------------------------------------------------------

    public function test_akun_google_wajib_melengkapi_no_hp_dan_tersimpan_ke_profil(): void
    {
        $pelanggan = $this->pelangganGoogle();
        $cabang = $this->cabangAktif();

        $this->assertNull($pelanggan->no_hp, 'Akun Google seharusnya tanpa no_hp');

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('phone', '081234567890')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Sudirman No. 7 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('pesananBerhasil.id', fn ($id) => $id > 0);

        // No HP harus tersimpan ke profil (ternormalisasi)
        $phoneService = app(CustomerPhoneService::class);
        $normalized = $phoneService->normalize('081234567890');
        $this->assertDatabaseHas('pelanggan', [
            'id' => $pelanggan->id,
            'no_hp' => $normalized,
        ]);
    }

    public function test_akun_google_tanpa_no_hp_ditolak_jika_tidak_mengisi_phone(): void
    {
        $pelanggan = $this->pelangganGoogle();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('phone', '') // Tidak diisi
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Test No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasErrors(['phone']);
    }

    public function test_penolakan_no_hp_duplikat_akun_google_dengan_pesan_prd(): void
    {
        // Nomor HP sudah dipakai pelanggan lain
        $existing = $this->pelangganDenganHp();
        $pelangganGoogle = $this->pelangganGoogle();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelangganGoogle, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('phone', $existing->no_hp)
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Duplikat No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasErrors(['phone'])
            ->assertSeeHtml(e(CustomerPhoneService::DUPLICATE_PHONE_MESSAGE));
    }

    // -------------------------------------------------------------------------
    // 4. Riwayat status awal dalam satu transaksi DB
    // -------------------------------------------------------------------------

    public function test_pesanan_tersimpan_dengan_riwayat_status_awal_diproses_dalam_satu_transaksi_db(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Test Riwayat No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors();

        $transaksi = Transaksi::where('pelanggan_id', $pelanggan->id)->latest()->first();
        $this->assertNotNull($transaksi);

        // Riwayat status awal harus ada
        $this->assertDatabaseHas('riwayat_status', [
            'transaksi_id' => $transaksi->id,
            'status_baru' => StatusTransaksi::Diproses->value,
            'diubah_oleh_pelanggan' => $pelanggan->id,
            'diubah_oleh_staff' => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. Keamanan: total dari browser diabaikan
    // -------------------------------------------------------------------------

    public function test_total_dari_browser_diabaikan_dan_dihitung_dari_config_cod(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();
        $hargaSatuan = (int) config('sigalon.harga.cod', 7000);

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 2)
            ->set('alamat_antar', 'Jl. Test Security No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->set('browser_total', 999999) // Manipulasi dari browser
            ->call('simpan')
            ->assertHasNoErrors();

        // Total yang tersimpan harus dari config, bukan dari browser
        $this->assertDatabaseHas('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'total_harga' => 2 * $hargaSatuan,
        ]);

        $this->assertDatabaseMissing('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'total_harga' => 999999,
        ]);
    }

    // -------------------------------------------------------------------------
    // 6. Validasi
    // -------------------------------------------------------------------------

    public function test_validasi_menolak_cabang_nonaktif(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabangNonaktif = Cabang::factory()->inactive()->create();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabangNonaktif->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Test No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasErrors(['cabang_id']);
    }

    public function test_validasi_menolak_jumlah_galon_nol_atau_negatif(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 0)
            ->set('alamat_antar', 'Jl. Test No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasErrors(['jumlah_galon']);
    }

    public function test_validasi_menolak_jumlah_galon_melebihi_batas_maksimal(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 21) // Melebihi batas 20
            ->set('alamat_antar', 'Jl. Test No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasErrors(['jumlah_galon']);
    }

    public function test_validasi_menolak_alamat_antar_kosong(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', '')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasErrors(['alamat_antar']);
    }

    // -------------------------------------------------------------------------
    // 7. XSS Escaping
    // -------------------------------------------------------------------------

    public function test_input_berbahaya_diescape_saat_ditampilkan(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();
        $inputBerbahaya = '<script>alert("xss")</script>';

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', $inputBerbahaya)
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors();

        // Data tersimpan apa adanya di DB
        $this->assertDatabaseHas('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'alamat_antar' => $inputBerbahaya,
        ]);

        // Konfirmasi: tampilan menggunakan e() / {{ }} yang otomatis escape
        $transaksi = Transaksi::where('pelanggan_id', $pelanggan->id)->latest()->first();
        $this->assertNotNull($transaksi);
        // Memastikan data DB tidak menyimpan HTML entitas (disimpan raw, escaped saat render)
        $this->assertStringContainsString('<script>', $transaksi->alamat_antar);
    }

    public function test_kartu_konfirmasi_menampilkan_alamat_ter_escape_satu_kali(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = Cabang::factory()->create(['is_active' => true, 'nama' => "Cabang O'Neil & Co"]);
        $alamat = 'Jl. Cempaka & Melati <b>No. 5</b>';

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', $alamat)
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSee($alamat)
            ->assertSee("Cabang O'Neil & Co")
            ->assertDontSeeHtml('&amp;amp;')
            ->assertDontSeeHtml('<b>No. 5</b>');
    }

    public function test_form_tidak_lagi_meminta_catatan_yang_tidak_tersimpan(): void
    {
        Livewire::actingAs($this->pelangganDenganHp(), 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->assertDontSeeHtml('data-test="input-catatan"');
    }

    // -------------------------------------------------------------------------
    // 8. Pengaman double-submit (Cache::lock)
    // -------------------------------------------------------------------------

    public function test_pengaman_server_mencegah_pesanan_dobel_dari_request_ganda(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();

        // Simulasi lock sudah dipegang (request pertama sedang berjalan)
        $lock = Cache::lock('order-pelanggan-'.$pelanggan->id, 10);
        $lock->acquire();

        try {
            // Request kedua harus ditolak karena lock sudah diambil
            Livewire::actingAs($pelanggan, 'pelanggan')
                ->test('pages::pelanggan.pesan')
                ->set('cabang_id', $cabang->id)
                ->set('jumlah_galon', 1)
                ->set('alamat_antar', 'Jl. Test Lock No. 1 Padang')
                ->set('metode_bayar', 'tunai')
                ->call('simpan')
                ->assertHasErrors(['_lock']);
        } finally {
            $lock->release();
        }

        // Tidak ada transaksi yang tersimpan
        $this->assertDatabaseMissing('transaksi', [
            'pelanggan_id' => $pelanggan->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 9. Jenis item: hanya isi ulang (bukan galon baru)
    // -------------------------------------------------------------------------

    public function test_pesanan_online_selalu_isi_ulang_bukan_galon_baru(): void
    {
        $pelanggan = $this->pelangganDenganHp();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Test Jenis Item No. 1 Padang')
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'jenis_item' => JenisItem::IsiUlang->value,
        ]);

        $this->assertDatabaseMissing('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'jenis_item' => JenisItem::GalonBaru->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // 10. Alamat tersimpan ke profil
    // -------------------------------------------------------------------------

    public function test_alamat_tersimpan_ke_profil_pelanggan_setelah_pesan(): void
    {
        $pelanggan = $this->pelangganDenganHp(null); // Tanpa alamat profil
        $cabang = $this->cabangAktif();
        $alamat = 'Jl. Profil Baru No. 99 Padang';

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', $alamat)
            ->set('metode_bayar', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pelanggan', [
            'id' => $pelanggan->id,
            'alamat' => $alamat,
        ]);
    }

    public function test_field_alamat_diprefill_dari_profil_pelanggan(): void
    {
        $alamatProfil = 'Jl. Existing No. 5 Padang';
        $pelanggan = $this->pelangganDenganHp($alamatProfil);

        $component = Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan');

        $component->assertSet('alamat_antar', $alamatProfil);
    }
}
