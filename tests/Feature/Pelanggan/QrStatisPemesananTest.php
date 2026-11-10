<?php

namespace Tests\Feature\Pelanggan;

use App\Enums\MetodePembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * US-14: Pembayaran QR statis pada pemesanan online.
 *
 * Sprint 2 — Memverifikasi tampilan QR statis saat metode QR dipilih,
 * sembunyi saat metode tunai, empty state ramah saat file belum ada,
 * teks non-verifikasi otomatis, dan bukti database tidak terverifikasi otomatis.
 */
class QrStatisPemesananTest extends TestCase
{
    use RefreshDatabase;

    private function cabangAktif(): Cabang
    {
        return Cabang::factory()->create(['is_active' => true]);
    }

    private function pelanggan(): Pelanggan
    {
        return Pelanggan::factory()->create([
            'alamat' => 'Jl. Khatib Sulaiman No. 10 Padang',
        ]);
    }

    public function test_qr_statis_tampil_bila_pelanggan_memilih_metode_qr(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr-statis.png')->getContent());

        $pelanggan = $this->pelanggan();
        $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->assertSet('metode_bayar', 'tunai')
            ->assertDontSeeHtml('data-test="container-qr-statis"')
            ->call('pilihMetode', 'qr')
            ->assertSet('metode_bayar', 'qr')
            ->assertSeeHtml('data-test="container-qr-statis"')
            ->assertSeeHtml('data-test="img-qr-statis"')
            ->assertSee(Storage::disk('public')->url('qr/qr-statis.png'), false);
    }

    public function test_qr_statis_sembunyi_bila_pelanggan_memilih_metode_tunai(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr-statis.png')->getContent());

        $pelanggan = $this->pelanggan();
        $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->call('pilihMetode', 'qr')
            ->assertSeeHtml('data-test="container-qr-statis"')
            ->call('pilihMetode', 'tunai')
            ->assertSet('metode_bayar', 'tunai')
            ->assertDontSeeHtml('data-test="container-qr-statis"')
            ->assertDontSeeHtml('data-test="img-qr-statis"');
    }

    public function test_empty_state_tampil_dengan_pesan_ramah_jika_file_qr_belum_diunggah(): void
    {
        Storage::fake('public');
        // Pastikan tidak ada file qr di storage public

        $pelanggan = $this->pelanggan();
        $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->call('pilihMetode', 'qr')
            ->assertSeeHtml('data-test="container-qr-statis"')
            ->assertSeeHtml('data-test="empty-qr-statis"')
            ->assertDontSeeHtml('data-test="img-qr-statis"')
            ->assertSee('QR pembayaran belum tersedia. Silakan pilih Tunai atau hubungi depot.');
    }

    public function test_halaman_menampilkan_edukasi_pembayaran_qr_tidak_diverifikasi_otomatis(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr-statis.png')->getContent());

        $pelanggan = $this->pelanggan();
        $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->call('pilihMetode', 'qr')
            ->assertSee('Pembayaran QR tidak diverifikasi otomatis oleh sistem')
            ->assertSee('simpan bukti transfer di ponsel Anda dan perlihatkan kepada kurir');
    }

    public function test_nominal_tagihan_pada_area_qr_sesuai_dengan_jumlah_galon(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr-statis.png')->getContent());

        $pelanggan = $this->pelanggan();
        $this->cabangAktif();

        $component = Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->call('pilihMetode', 'qr')
            ->assertSee('Rp7.000');

        $component->call('setJumlah', 3)
            ->assertSee('Rp21.000');
    }

    public function test_pesanan_dengan_metode_qr_tersimpan_dengan_metode_bayar_qr_dan_tidak_terverifikasi_otomatis(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr-statis.png')->getContent());

        $pelanggan = $this->pelanggan();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 2)
            ->set('alamat_antar', 'Jl. Ahmad Yani No. 15 Padang')
            ->call('pilihMetode', 'qr')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('pesananBerhasil.metode_kode', 'qr');

        // Verifikasi database: pesanan tersimpan berstatus 'diproses', diverifikasi_oleh tetap null (tidak terverifikasi otomatis)
        $this->assertDatabaseHas('transaksi', [
            'pelanggan_id' => $pelanggan->id,
            'cabang_id' => $cabang->id,
            'metode_bayar' => MetodePembayaran::Qr->value,
            'status' => StatusTransaksi::Diproses->value,
            'diverifikasi_oleh' => null,
            'dicatat_oleh' => null,
        ]);
    }

    public function test_kartu_konfirmasi_pesanan_berhasil_menampilkan_qr_statis_bila_metode_qr(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr-statis.png')->getContent());

        $pelanggan = $this->pelanggan();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 2)
            ->set('alamat_antar', 'Jl. Ahmad Yani No. 15 Padang')
            ->call('pilihMetode', 'qr')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSeeHtml('data-test="konfirmasi-qr-statis"')
            ->assertSeeHtml('data-test="konfirmasi-img-qr-statis"')
            ->assertSee('Pembayaran QR tidak diverifikasi otomatis');
    }

    public function test_kartu_konfirmasi_pesanan_berhasil_tidak_menampilkan_qr_statis_bila_metode_tunai(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr-statis.png')->getContent());

        $pelanggan = $this->pelanggan();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Ahmad Yani No. 15 Padang')
            ->call('pilihMetode', 'tunai')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertDontSeeHtml('data-test="konfirmasi-qr-statis"');
    }

    public function test_kartu_konfirmasi_pesanan_berhasil_menampilkan_pesan_netral_bila_file_qr_belum_tersedia(): void
    {
        Storage::fake('public');
        // File QR sengaja tidak ada

        $pelanggan = $this->pelanggan();
        $cabang = $this->cabangAktif();

        Livewire::actingAs($pelanggan, 'pelanggan')
            ->test('pages::pelanggan.pesan')
            ->set('cabang_id', $cabang->id)
            ->set('jumlah_galon', 1)
            ->set('alamat_antar', 'Jl. Ahmad Yani No. 15 Padang')
            ->call('pilihMetode', 'qr')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSeeHtml('data-test="konfirmasi-qr-statis"')
            ->assertSeeHtml('data-test="konfirmasi-empty-qr-statis"')
            ->assertSee('QR pembayaran belum tersedia. Silakan pilih Tunai atau hubungi depot.');
    }

    public function test_qr_code_service_mendeteksi_berbagai_ekstensi_file(): void
    {
        Storage::fake('public');
        $service = app(QrCodeService::class);

        $this->assertFalse($service->hasQrCode());
        $this->assertNull($service->getQrCodeUrl());

        // Test format jpg
        Storage::disk('public')->put('qr/qr-statis.jpg', 'fake-image-content');
        $this->assertTrue($service->hasQrCode());
        $this->assertStringContainsString('storage/qr/qr-statis.jpg', (string) $service->getQrCodeUrl());

        // Test prioritas png jika keduanya ada
        Storage::disk('public')->put('qr/qr-statis.png', 'fake-image-content');
        $this->assertStringContainsString('storage/qr/qr-statis.png', (string) $service->getQrCodeUrl());
    }
}
