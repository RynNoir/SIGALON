<?php

namespace Tests\Feature\Cabang;

use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\RiwayatStatus;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PesananTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function stafBerhak(): array
    {
        return [
            'kasir' => ['kasir'],
            'admin cabang' => ['adminCabang'],
        ];
    }

    #[DataProvider('stafBerhak')]
    public function test_branch_staff_see_manual_and_online_orders_of_own_branch(string $peran): void
    {
        $cabang = Cabang::factory()->create();
        $staf = User::factory()->{$peran}()->for($cabang)->create();
        $this->pesanan($cabang, StatusTransaksi::Diproses, ['nama_pemesan' => 'Pemesan Manual', 'jumlah_galon' => 2, 'kurir_id' => null]);
        $this->pesanan($cabang, StatusTransaksi::Diproses, ['nama_pemesan' => 'Pemesan Online', 'pelanggan_id' => Pelanggan::factory()->create()->id, 'kurir_id' => null]);
        $this->pesanan(Cabang::factory()->create(), StatusTransaksi::Diproses, ['nama_pemesan' => 'Pemesan Cabang Lain']);

        $this->actingAs($staf)->get(route('cabang.pesanan'))
            ->assertOk()
            ->assertSee('Daftar Pesanan')
            ->assertSee('Pemesan Manual')
            ->assertSee('Manual')
            ->assertSee('Pemesan Online')
            ->assertSee('Online')
            ->assertSee('Rp14.000')
            ->assertSee('Menunggu kurir')
            ->assertDontSee('Pemesan Cabang Lain');
    }

    public function test_walk_in_and_canvassing_are_not_listed(): void
    {
        $cabang = Cabang::factory()->create();
        Transaksi::factory()->for($cabang)->create();
        Transaksi::factory()->for($cabang)->canvassing()->create();
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->set('tab', 'selesai')
            ->assertSeeHtml('data-test="pesanan-empty"');
    }

    public function test_empty_state_is_shown_when_branch_has_no_orders(): void
    {
        $this->actingAs(User::factory()->kasir()->create());

        Livewire::test('pages::cabang.pesanan')
            ->assertSeeHtml('data-test="pesanan-empty"')
            ->assertSee('Belum ada pesanan pada tab ini');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('cabang.pesanan'))->assertRedirect(route('login'));
    }

    public function test_super_admin_is_forbidden(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('cabang.pesanan'))
            ->assertForbidden();
    }

    public function test_courier_is_sent_to_access_denied_page(): void
    {
        $this->actingAs(User::factory()->kurir()->create())
            ->get(route('cabang.pesanan'))
            ->assertRedirect(route('akses-ditolak'));
    }

    public function test_customer_is_forbidden(): void
    {
        $this->actingAs(Pelanggan::factory()->create(), 'pelanggan')
            ->get(route('cabang.pesanan'))
            ->assertForbidden();
    }

    public function test_later_requests_are_rejected_when_role_no_longer_qualifies(): void
    {
        $cabang = Cabang::factory()->create();
        $staf = User::factory()->kasir()->for($cabang)->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $this->actingAs($staf);
        $halaman = Livewire::test('pages::cabang.pesanan');

        $staf->forceFill(['role' => 'super_admin', 'cabang_id' => null])->save();
        $halaman->call('ubahStatus', $pesanan->id, 'dijemput');

        $halaman->assertForbidden();
        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
    }

    public function test_staff_of_inactive_branch_see_safe_message_and_cannot_act(): void
    {
        $cabang = Cabang::factory()->create(['is_active' => false]);
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses, ['nama_pemesan' => 'Pemesan Cabang Nonaktif']);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->assertSeeHtml('data-test="alert-cabang-nonaktif"')
            ->assertDontSee('Pemesan Cabang Nonaktif')
            ->call('ubahStatus', $pesanan->id, 'dijemput')
            ->assertForbidden();

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
    }

    public function test_sidebar_link_is_shown_only_to_branch_staff(): void
    {
        $url = 'href="'.route('cabang.pesanan').'"';

        $this->actingAs(User::factory()->kasir()->create())->get(route('dashboard'))->assertSee($url, false);
        $this->actingAs(User::factory()->adminCabang()->create())->get(route('dashboard'))->assertSee($url, false);
        $this->actingAs(User::factory()->superAdmin()->create())->get(route('dashboard'))->assertDontSee($url, false);
    }

    public function test_manual_order_button_is_shown_only_to_cashier(): void
    {
        $this->actingAs(User::factory()->kasir()->create());
        Livewire::test('pages::cabang.pesanan')->assertSeeHtml('data-test="tambah-pesanan-manual"');

        $this->actingAs(User::factory()->adminCabang()->create());
        Livewire::test('pages::cabang.pesanan')->assertDontSeeHtml('data-test="tambah-pesanan-manual"');
    }

    /**
     * @return array<string, array{0: string, 1: list<StatusTransaksi>}>
     */
    public static function isiTab(): array
    {
        return [
            'aktif' => ['aktif', [StatusTransaksi::Diproses, StatusTransaksi::Dijemput, StatusTransaksi::Diantar]],
            'diproses' => ['diproses', [StatusTransaksi::Diproses]],
            'dijemput' => ['dijemput', [StatusTransaksi::Dijemput]],
            'diantar' => ['diantar', [StatusTransaksi::Diantar]],
            'selesai' => ['selesai', [StatusTransaksi::Selesai]],
            'dibatalkan' => ['dibatalkan', [StatusTransaksi::Dibatalkan]],
        ];
    }

    /**
     * @param  list<StatusTransaksi>  $statusDiharapkan
     */
    #[DataProvider('isiTab')]
    public function test_tab_lists_only_orders_with_matching_status_and_shows_counts(string $tab, array $statusDiharapkan): void
    {
        $cabang = Cabang::factory()->create();
        foreach ([StatusTransaksi::Diproses, StatusTransaksi::Dijemput, StatusTransaksi::Diantar, StatusTransaksi::Selesai, StatusTransaksi::Dibatalkan] as $status) {
            $this->pesanan($cabang, $status);
        }
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        $halaman = Livewire::test('pages::cabang.pesanan')->set('tab', $tab);

        $this->assertEqualsCanonicalizing(
            array_map(fn (StatusTransaksi $status): string => $status->value, $statusDiharapkan),
            collect($halaman->instance()->pesananList->items())->map(fn (Transaksi $pesanan): string => $pesanan->status->value)->all(),
        );
        $halaman->assertSeeHtml('data-test="jumlah-aktif"')
            ->assertSet('tab', $tab);
        $this->assertSame(
            ['aktif' => 3, 'diproses' => 1, 'dijemput' => 1, 'diantar' => 1, 'selesai' => 1, 'dibatalkan' => 1],
            $halaman->instance()->jumlahPerTab,
        );
    }

    public function test_unknown_tab_falls_back_to_active_orders(): void
    {
        $this->actingAs(User::factory()->kasir()->create());

        Livewire::withQueryParams(['tab' => 'semua-cabang'])
            ->test('pages::cabang.pesanan')
            ->assertSet('tab', 'aktif')
            ->set('tab', '<script>')
            ->assertSet('tab', 'aktif');
    }

    public function test_running_orders_start_from_oldest_and_finished_orders_from_newest(): void
    {
        $cabang = Cabang::factory()->create();
        $aktifLama = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $aktifBaru = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $selesaiLama = $this->pesanan($cabang, StatusTransaksi::Selesai);
        $selesaiBaru = $this->pesanan($cabang, StatusTransaksi::Selesai);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        $halaman = Livewire::test('pages::cabang.pesanan');
        $this->assertSame([$aktifLama->id, $aktifBaru->id], collect($halaman->instance()->pesananList->items())->pluck('id')->all());

        $halaman->set('tab', 'selesai');
        $this->assertSame([$selesaiBaru->id, $selesaiLama->id], collect($halaman->instance()->pesananList->items())->pluck('id')->all());
    }

    public function test_list_is_paginated_and_changing_tab_returns_to_first_page(): void
    {
        $cabang = Cabang::factory()->create();
        Transaksi::factory()->count(11)->for($cabang)->cod(StatusTransaksi::Diproses)->create();
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        $halaman = Livewire::test('pages::cabang.pesanan');
        $this->assertCount(10, $halaman->instance()->pesananList->items());

        $halaman->call('gotoPage', 2);
        $this->assertCount(1, $halaman->instance()->pesananList->items());

        $halaman->set('tab', 'diproses');
        $this->assertSame(1, $halaman->instance()->pesananList->currentPage());
    }

    public function test_page_falls_back_to_last_page_when_current_page_becomes_empty(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = Transaksi::factory()->count(11)->for($cabang)->cod(StatusTransaksi::Diproses)->create();
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());
        $halaman = Livewire::test('pages::cabang.pesanan')->call('gotoPage', 2);

        $halaman->call('bukaBatalkan', $pesanan->last()->id)
            ->set('alasan', 'Pemesan membatalkan')
            ->call('simpanBatal');

        $this->assertCount(10, $halaman->instance()->pesananList->items());
        $this->assertSame(1, $halaman->instance()->pesananList->currentPage());
    }

    /**
     * @return array<string, array{0: StatusTransaksi, 1: bool, 2: list<string>, 3: list<string>}>
     */
    public static function aksiPerStatus(): array
    {
        return [
            'diproses tanpa kurir' => [StatusTransaksi::Diproses, false, ['Pilih Kurir', 'Batalkan'], ['Tandai Dijemput', 'Ganti Kurir']],
            'diproses dengan kurir' => [StatusTransaksi::Diproses, true, ['Tandai Dijemput', 'Ganti Kurir', 'Batalkan'], ['Pilih Kurir']],
            'dijemput' => [StatusTransaksi::Dijemput, true, ['Tandai Diantar'], ['Tandai Selesai', 'Ganti Kurir', 'Batalkan']],
            'diantar' => [StatusTransaksi::Diantar, true, ['Tandai Selesai'], ['Tandai Diantar', 'Ganti Kurir', 'Batalkan']],
            'selesai' => [StatusTransaksi::Selesai, true, [], ['Tandai', 'Ganti Kurir', 'Batalkan']],
            'dibatalkan' => [StatusTransaksi::Dibatalkan, true, [], ['Tandai', 'Ganti Kurir', 'Pilih Kurir']],
        ];
    }

    /**
     * @param  list<string>  $tampil
     * @param  list<string>  $tersembunyi
     */
    #[DataProvider('aksiPerStatus')]
    public function test_only_valid_actions_are_shown_for_each_status(StatusTransaksi $status, bool $adaKurir, array $tampil, array $tersembunyi): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, $status, $adaKurir ? [] : ['kurir_id' => null]);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        $baris = $this->ambilBarisPesanan($pesanan, $status);

        foreach ($tampil as $teks) {
            $this->assertStringContainsString($teks, $baris);
        }
        foreach ($tersembunyi as $teks) {
            $this->assertStringNotContainsString($teks, $baris);
        }
    }

    public function test_finishing_an_order_asks_for_confirmation(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diantar);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->assertSeeHtml('wire:confirm="Tandai pesanan #COD-'.$pesanan->id.' selesai?');
    }

    #[DataProvider('stafBerhak')]
    public function test_branch_staff_can_assign_active_courier_of_own_branch(string $peran): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses, ['kurir_id' => null]);
        $kurir = User::factory()->kurir()->for($cabang)->create();
        $this->actingAs(User::factory()->{$peran}()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->call('bukaTugaskanKurir', $pesanan->id)
            ->assertSet('modalKurir', true)
            ->set('kurirId', (string) $kurir->id)
            ->call('simpanKurir')
            ->assertHasNoErrors()
            ->assertSet('modalKurir', false);

        $this->assertSame($kurir->id, $pesanan->fresh()?->kurir_id);
        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_courier_options_contain_only_active_couriers_of_own_branch(): void
    {
        $cabang = Cabang::factory()->create();
        $kurirAktif = User::factory()->kurir()->for($cabang)->create();
        User::factory()->kurir()->inactive()->for($cabang)->create();
        User::factory()->kurir()->for(Cabang::factory()->create())->create();
        User::factory()->kasir()->for($cabang)->create();
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        $halaman = Livewire::test('pages::cabang.pesanan');

        $this->assertSame([$kurirAktif->id], $halaman->instance()->kurirAktif->pluck('id')->all());
    }

    public function test_branch_without_active_courier_gets_a_clear_message(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses, ['kurir_id' => null]);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->call('bukaTugaskanKurir', $pesanan->id)
            ->assertSeeHtml('data-test="kurir-kosong"')
            ->assertDontSeeHtml('data-test="simpan-kurir"');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function pilihanKurirTidakSah(): array
    {
        return [
            'kosong' => ['kosong'],
            'tidak ada' => ['tidak-ada'],
            'kurir cabang lain' => ['cabang-lain'],
        ];
    }

    #[DataProvider('pilihanKurirTidakSah')]
    public function test_invalid_courier_selection_keeps_modal_open_with_error(string $pilihan): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses, ['kurir_id' => null]);
        $kurirId = match ($pilihan) {
            'kosong' => '',
            'tidak-ada' => '999999',
            default => (string) User::factory()->kurir()->for(Cabang::factory()->create())->create()->id,
        };
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->call('bukaTugaskanKurir', $pesanan->id)
            ->set('kurirId', $kurirId)
            ->call('simpanKurir')
            ->assertHasErrors(['kurir_id'])
            ->assertSet('modalKurir', true);

        $this->assertNull($pesanan->fresh()?->kurir_id);
    }

    /**
     * @return array<string, array{0: StatusTransaksi, 1: StatusTransaksi}>
     */
    public static function langkahStatus(): array
    {
        return [
            'dijemput' => [StatusTransaksi::Diproses, StatusTransaksi::Dijemput],
            'diantar' => [StatusTransaksi::Dijemput, StatusTransaksi::Diantar],
            'selesai' => [StatusTransaksi::Diantar, StatusTransaksi::Selesai],
        ];
    }

    #[DataProvider('langkahStatus')]
    public function test_advancing_status_updates_order_and_records_history(StatusTransaksi $statusLama, StatusTransaksi $statusBaru): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, $statusLama);
        $kasir = User::factory()->kasir()->for($cabang)->create();
        $this->actingAs($kasir);

        Livewire::test('pages::cabang.pesanan')
            ->call('ubahStatus', $pesanan->id, $statusBaru->value)
            ->assertHasNoErrors();

        $this->assertSame($statusBaru, $pesanan->fresh()?->status);
        $riwayat = RiwayatStatus::query()->sole();
        $this->assertSame($statusLama, $riwayat->status_lama);
        $this->assertSame($statusBaru, $riwayat->status_baru);
        $this->assertSame($kasir->id, $riwayat->diubah_oleh_staff);
    }

    public function test_stale_page_gets_a_message_and_shows_the_latest_status(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());
        $halaman = Livewire::test('pages::cabang.pesanan');
        $pesanan->forceFill(['status' => StatusTransaksi::Dijemput])->save();

        $halaman->call('ubahStatus', $pesanan->id, 'dijemput');

        $halaman->assertHasErrors(['status'])
            ->assertSeeHtml('data-test="error-status"')
            ->assertSee('Tandai Diantar');
        $this->assertSame(StatusTransaksi::Dijemput, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_request_skipping_a_status_is_rejected(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->call('ubahStatus', $pesanan->id, 'selesai')
            ->assertHasErrors(['status']);

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
    }

    public function test_unknown_target_status_is_rejected_with_a_message(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->call('ubahStatus', $pesanan->id, 'status-palsu')
            ->assertHasErrors(['status']);

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
    }

    public function test_next_action_clears_the_previous_error(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->call('ubahStatus', $pesanan->id, 'selesai')
            ->assertHasErrors(['status'])
            ->call('ubahStatus', $pesanan->id, 'dijemput')
            ->assertHasNoErrors();
    }

    #[DataProvider('stafBerhak')]
    public function test_branch_staff_can_cancel_and_the_reason_is_shown(string $peran): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $staf = User::factory()->{$peran}()->for($cabang)->create();
        $this->actingAs($staf);

        $halaman = Livewire::test('pages::cabang.pesanan')
            ->call('bukaBatalkan', $pesanan->id)
            ->assertSet('modalBatal', true)
            ->set('alasan', 'Pemesan tidak jadi membeli')
            ->call('simpanBatal')
            ->assertHasNoErrors()
            ->assertSet('modalBatal', false);

        $this->assertSame(StatusTransaksi::Dibatalkan, $pesanan->fresh()?->status);
        $riwayat = RiwayatStatus::query()->sole();
        $this->assertSame('Pemesan tidak jadi membeli', $riwayat->catatan);
        $this->assertSame($staf->id, $riwayat->diubah_oleh_staff);

        $halaman->set('tab', 'dibatalkan')
            ->assertSee('Pemesan tidak jadi membeli');
    }

    public function test_cancelling_without_reason_keeps_modal_open_with_error(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->call('bukaBatalkan', $pesanan->id)
            ->set('alasan', '   ')
            ->call('simpanBatal')
            ->assertHasErrors(['alasan'])
            ->assertSet('modalBatal', true);

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_order_past_processing_cannot_be_cancelled_from_a_stale_page(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());
        $halaman = Livewire::test('pages::cabang.pesanan')->call('bukaBatalkan', $pesanan->id);
        $pesanan->forceFill(['status' => StatusTransaksi::Dijemput])->save();

        $halaman->set('alasan', 'Pemesan membatalkan')->call('simpanBatal');

        $halaman->assertHasErrors(['status'])
            ->assertSet('modalBatal', true)
            ->assertSee('Pesanan hanya bisa dibatalkan selama statusnya masih Diproses.');
        $this->assertSame(StatusTransaksi::Dijemput, $pesanan->fresh()?->status);
    }

    public function test_courier_cannot_be_changed_from_a_stale_page(): void
    {
        $cabang = Cabang::factory()->create();
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses);
        $kurirLama = $pesanan->kurir_id;
        $kurirBaru = User::factory()->kurir()->for($cabang)->create();
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());
        $halaman = Livewire::test('pages::cabang.pesanan')->call('bukaTugaskanKurir', $pesanan->id);
        $pesanan->forceFill(['status' => StatusTransaksi::Dijemput])->save();

        $halaman->set('kurirId', (string) $kurirBaru->id)->call('simpanKurir');

        $halaman->assertHasErrors(['status'])
            ->assertSee('Kurir hanya bisa ditugaskan selama pesanan masih Diproses.');
        $this->assertSame($kurirLama, $pesanan->fresh()?->kurir_id);
    }

    /**
     * @return array<string, array{0: string, 1: list<mixed>}>
     */
    public static function aksiLangsung(): array
    {
        return [
            'ubah status' => ['ubahStatus', ['dijemput']],
            'buka pilih kurir' => ['bukaTugaskanKurir', []],
            'buka batalkan' => ['bukaBatalkan', []],
        ];
    }

    /**
     * @param  list<mixed>  $argumen
     */
    #[DataProvider('aksiLangsung')]
    public function test_actions_on_order_of_another_branch_return_404(string $aksi, array $argumen): void
    {
        $pesananLain = $this->pesanan(Cabang::factory()->create(), StatusTransaksi::Diproses);
        $this->actingAs(User::factory()->kasir()->create());

        Livewire::test('pages::cabang.pesanan')
            ->call($aksi, $pesananLain->id, ...$argumen)
            ->assertNotFound();

        $this->assertSame(StatusTransaksi::Diproses, $pesananLain->fresh()?->status);
    }

    public function test_tampered_order_id_in_modal_cannot_touch_another_branch(): void
    {
        $cabang = Cabang::factory()->create();
        $pesananLain = $this->pesanan(Cabang::factory()->create(), StatusTransaksi::Diproses);
        $kurirLama = $pesananLain->kurir_id;
        $kurir = User::factory()->kurir()->for($cabang)->create();
        $this->actingAs(User::factory()->kasir()->for($cabang)->create());

        Livewire::test('pages::cabang.pesanan')
            ->set('pesananKurirId', $pesananLain->id)
            ->set('kurirId', (string) $kurir->id)
            ->call('simpanKurir')
            ->assertNotFound();

        Livewire::test('pages::cabang.pesanan')
            ->set('pesananBatalId', $pesananLain->id)
            ->set('alasan', 'Alasan uji')
            ->call('simpanBatal')
            ->assertNotFound();

        $this->assertSame($kurirLama, $pesananLain->fresh()?->kurir_id);
        $this->assertSame(StatusTransaksi::Diproses, $pesananLain->fresh()?->status);
    }

    public function test_user_provided_text_is_escaped(): void
    {
        $cabang = Cabang::factory()->create();
        $skrip = "<script>alert('xss')</script>";
        $pesanan = $this->pesanan($cabang, StatusTransaksi::Diproses, ['nama_pemesan' => $skrip, 'alamat_antar' => 'Jl. '.$skrip]);
        $batal = $this->pesanan($cabang, StatusTransaksi::Dibatalkan);
        RiwayatStatus::factory()->for($batal)->create(['status_lama' => StatusTransaksi::Diproses, 'status_baru' => StatusTransaksi::Dibatalkan, 'catatan' => $skrip]);
        $kasir = User::factory()->kasir()->for($cabang)->create();

        $this->actingAs($kasir)->get(route('cabang.pesanan'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee($skrip, false);
        $this->actingAs($kasir)->get(route('cabang.pesanan', ['tab' => 'dibatalkan']))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee($skrip, false);

        $this->assertModelExists($pesanan);
    }

    /**
     * @param  array<string, mixed>  $atribut
     */
    private function pesanan(Cabang $cabang, StatusTransaksi $status, array $atribut = []): Transaksi
    {
        return Transaksi::factory()->for($cabang)->cod($status)->create($atribut);
    }

    /**
     * HTML satu baris pesanan pada tab yang memuat statusnya.
     */
    private function ambilBarisPesanan(Transaksi $pesanan, StatusTransaksi $status): string
    {
        $html = Livewire::test('pages::cabang.pesanan')->set('tab', $status->value)->html();
        $awal = (int) strpos($html, 'data-test="pesanan-'.$pesanan->id.'"');

        return substr($html, $awal, (int) strpos($html, '</tr>', $awal) - $awal);
    }
}
