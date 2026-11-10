<?php

namespace Tests\Feature\Admin;

use App\Models\Pelanggan;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QrStatisTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_empty_state_before_any_qr_is_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('admin.qr-statis'))
            ->assertOk()
            ->assertSee('Gambar QR Statis')
            ->assertSee('Unggah gambar QR')
            ->assertSeeHtml('data-test="qr-kosong"');
    }

    public function test_super_admin_uploads_qr_that_customers_will_see(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test('pages::admin.qr-statis')
            ->set('gambar', UploadedFile::fake()->image('qris.png', 400, 400))
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('gambar', null)
            ->assertSeeHtml('data-test="qr-saat-ini"')
            ->assertSee('Ganti gambar QR');

        Storage::disk('public')->assertExists('qr/qr-statis.png');
        $this->assertSame('qr/qr-statis.png', app(QrCodeService::class)->getQrCodePath());
    }

    public function test_replacing_with_another_format_removes_the_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('lama.png')->getContent());
        Storage::disk('public')->put('qr/qris-statis.webp', UploadedFile::fake()->image('lama.webp')->getContent());
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test('pages::admin.qr-statis')
            ->set('gambar', UploadedFile::fake()->image('baru.jpg'))
            ->call('simpan')
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists('qr/qr-statis.jpg');
        Storage::disk('public')->assertMissing('qr/qr-statis.png');
        Storage::disk('public')->assertMissing('qr/qris-statis.webp');
        $this->assertSame('qr/qr-statis.jpg', app(QrCodeService::class)->getQrCodePath());
    }

    public function test_qr_url_carries_a_version_so_browsers_show_the_new_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qr/qr-statis.png', UploadedFile::fake()->image('qr.png')->getContent());

        $url = app(QrCodeService::class)->getQrCodeUrl();

        $this->assertStringStartsWith(Storage::disk('public')->url('qr/qr-statis.png').'?v=', (string) $url);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function fileTidakSah(): array
    {
        return [
            'kosong' => ['kosong'],
            'pdf' => ['pdf'],
            'svg' => ['svg'],
            'lebih dari 2 MB' => ['besar'],
        ];
    }

    #[DataProvider('fileTidakSah')]
    public function test_invalid_file_is_rejected_and_nothing_is_stored(string $jenis): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());
        $halaman = Livewire::test('pages::admin.qr-statis');

        match ($jenis) {
            'pdf' => $halaman->set('gambar', UploadedFile::fake()->create('qr.pdf', 100, 'application/pdf')),
            'svg' => $halaman->set('gambar', UploadedFile::fake()->create('qr.svg', 10, 'image/svg+xml')),
            'besar' => $halaman->set('gambar', UploadedFile::fake()->image('qr.png')->size(2049)),
            default => null,
        };
        $halaman->call('simpan')->assertHasErrors(['gambar']);

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_file_exactly_at_the_size_limit_is_accepted(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test('pages::admin.qr-statis')
            ->set('gambar', UploadedFile::fake()->image('qr.png')->size(2048))
            ->call('simpan')
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists('qr/qr-statis.png');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function stafCabang(): array
    {
        return [
            'admin cabang' => ['adminCabang'],
            'kasir' => ['kasir'],
        ];
    }

    #[DataProvider('stafCabang')]
    public function test_branch_staff_are_forbidden(string $peran): void
    {
        $this->actingAs(User::factory()->{$peran}()->create())
            ->get(route('admin.qr-statis'))
            ->assertForbidden();
    }

    public function test_courier_is_sent_to_access_denied_page(): void
    {
        $this->actingAs(User::factory()->kurir()->create())
            ->get(route('admin.qr-statis'))
            ->assertRedirect(route('akses-ditolak'));
    }

    public function test_customer_is_forbidden_and_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.qr-statis'))->assertRedirect(route('login'));

        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->get(route('admin.qr-statis'))
            ->assertForbidden();
    }

    public function test_upload_is_rejected_when_role_no_longer_qualifies(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);
        $halaman = Livewire::test('pages::admin.qr-statis');

        $superAdmin->forceFill(['role' => 'admin_cabang'])->save();
        $halaman->call('simpan');

        $halaman->assertForbidden();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_sidebar_link_is_shown_only_to_super_admin(): void
    {
        $tautan = 'href="'.route('admin.qr-statis').'"';

        $this->actingAs(User::factory()->superAdmin()->create())->get(route('dashboard'))->assertSee($tautan, false);
        $this->actingAs(User::factory()->adminCabang()->create())->get(route('dashboard'))->assertDontSee($tautan, false);
    }
}
