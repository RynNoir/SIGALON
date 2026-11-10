<?php

namespace Tests\Feature\Admin;

use App\Models\Cabang;
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

    public function test_super_admin_sees_qr_status_of_every_branch(): void
    {
        Storage::fake('public');
        $cabangDenganQr = Cabang::factory()->create(['nama' => 'Cabang Dengan QR']);
        $cabangTanpaQr = Cabang::factory()->create(['nama' => 'Cabang Tanpa QR']);
        Storage::disk('public')->put('qr/cabang-'.$cabangDenganQr->id.'.png', UploadedFile::fake()->image('qr.png')->getContent());

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('admin.qr-statis'))
            ->assertOk()
            ->assertSee('Gambar QR Statis per Cabang')
            ->assertSee('Cabang Dengan QR')
            ->assertSee('Cabang Tanpa QR')
            ->assertSeeHtml('data-test="img-qr-cabang-'.$cabangDenganQr->id.'"')
            ->assertDontSeeHtml('data-test="img-qr-cabang-'.$cabangTanpaQr->id.'"')
            ->assertSee('Belum ada QR');
    }

    public function test_page_explains_when_no_branch_exists(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('admin.qr-statis'))
            ->assertOk()
            ->assertSeeHtml('data-test="cabang-kosong"');
    }

    public function test_super_admin_uploads_qr_for_one_branch_only(): void
    {
        Storage::fake('public');
        $cabang = Cabang::factory()->create();
        $cabangLain = Cabang::factory()->create();
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test('pages::admin.qr-statis')
            ->set('cabangId', $cabang->id)
            ->set('gambar', UploadedFile::fake()->image('qris.png', 400, 400))
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('gambar', null)
            ->assertSeeHtml('data-test="img-qr-cabang-'.$cabang->id.'"');

        Storage::disk('public')->assertExists('qr/cabang-'.$cabang->id.'.png');
        $this->assertSame('qr/cabang-'.$cabang->id.'.png', app(QrCodeService::class)->getQrCodePath($cabang));
        $this->assertNull(app(QrCodeService::class)->getQrCodePath($cabangLain));
    }

    public function test_replacing_one_branch_qr_keeps_other_branches_and_removes_old_format(): void
    {
        Storage::fake('public');
        $cabang = Cabang::factory()->create();
        $cabangLain = Cabang::factory()->create();
        $disk = Storage::disk('public');
        $disk->put('qr/cabang-'.$cabang->id.'.png', UploadedFile::fake()->image('lama.png')->getContent());
        $disk->put('qr/cabang-'.$cabangLain->id.'.png', UploadedFile::fake()->image('lain.png')->getContent());
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test('pages::admin.qr-statis')
            ->set('cabangId', $cabang->id)
            ->set('gambar', UploadedFile::fake()->image('baru.jpg'))
            ->call('simpan')
            ->assertHasNoErrors();

        $disk->assertExists('qr/cabang-'.$cabang->id.'.jpg');
        $disk->assertMissing('qr/cabang-'.$cabang->id.'.png');
        $disk->assertExists('qr/cabang-'.$cabangLain->id.'.png');
    }

    public function test_qr_url_carries_a_version_so_browsers_show_the_new_image(): void
    {
        Storage::fake('public');
        $cabang = Cabang::factory()->create();
        Storage::disk('public')->put('qr/cabang-'.$cabang->id.'.png', UploadedFile::fake()->image('qr.png')->getContent());

        $url = app(QrCodeService::class)->getQrCodeUrl($cabang);

        $this->assertStringStartsWith(Storage::disk('public')->url('qr/cabang-'.$cabang->id.'.png').'?v=', (string) $url);
    }

    public function test_branch_must_be_chosen_and_must_exist(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test('pages::admin.qr-statis')
            ->set('gambar', UploadedFile::fake()->image('qr.png'))
            ->call('simpan')
            ->assertHasErrors(['cabangId' => 'required'])
            ->set('cabangId', 999999)
            ->call('simpan')
            ->assertHasErrors(['cabangId' => 'exists']);

        $this->assertSame([], Storage::disk('public')->allFiles());
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
        $cabang = Cabang::factory()->create();
        $this->actingAs(User::factory()->superAdmin()->create());
        $halaman = Livewire::test('pages::admin.qr-statis')->set('cabangId', $cabang->id);

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
        $cabang = Cabang::factory()->create();
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test('pages::admin.qr-statis')
            ->set('cabangId', $cabang->id)
            ->set('gambar', UploadedFile::fake()->image('qr.png')->size(2048))
            ->call('simpan')
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists('qr/cabang-'.$cabang->id.'.png');
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
        $cabang = Cabang::factory()->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $this->actingAs($superAdmin);
        $halaman = Livewire::test('pages::admin.qr-statis')->set('cabangId', $cabang->id);

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
