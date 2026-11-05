<?php

namespace Tests\Feature\Transaksi;

use App\Enums\Kanal;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\RiwayatStatus;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\StatusPesananService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class StatusPesananServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_advances_in_sequence_and_every_step_is_recorded(): void
    {
        $this->freezeSecond();
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $service = new StatusPesananService;

        $service->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kasir);
        $service->ubahStatus($pesanan, StatusTransaksi::Diantar, $kasir);
        $hasil = $service->ubahStatus($pesanan, StatusTransaksi::Selesai, $kasir);

        $this->assertSame(StatusTransaksi::Selesai, $hasil->status);
        $this->assertSame(StatusTransaksi::Selesai, $pesanan->fresh()?->status);

        $riwayat = $pesanan->riwayatStatus()->get();
        $this->assertSame(
            [
                ['diproses', 'dijemput'],
                ['dijemput', 'diantar'],
                ['diantar', 'selesai'],
            ],
            $riwayat->map(fn (RiwayatStatus $baris): array => [$baris->status_lama?->value, $baris->status_baru->value])->all(),
        );
        $this->assertTrue($riwayat->every(fn (RiwayatStatus $baris): bool => $baris->diubah_oleh_staff === $kasir->id
            && $baris->diubah_oleh_pelanggan === null
            && $baris->catatan === null
            && $baris->created_at->equalTo(now())));
    }

    /**
     * @return array<string, array{0: StatusTransaksi, 1: StatusTransaksi}>
     */
    public static function transisiTerlarang(): array
    {
        return [
            'lompat dari diproses ke diantar' => [StatusTransaksi::Diproses, StatusTransaksi::Diantar],
            'lompat dari diproses ke selesai' => [StatusTransaksi::Diproses, StatusTransaksi::Selesai],
            'lompat dari dijemput ke selesai' => [StatusTransaksi::Dijemput, StatusTransaksi::Selesai],
            'mundur dari dijemput ke diproses' => [StatusTransaksi::Dijemput, StatusTransaksi::Diproses],
            'mundur dari diantar ke dijemput' => [StatusTransaksi::Diantar, StatusTransaksi::Dijemput],
            'status yang sama' => [StatusTransaksi::Diproses, StatusTransaksi::Diproses],
            'selesai adalah status akhir' => [StatusTransaksi::Selesai, StatusTransaksi::Diproses],
            'dibatalkan adalah status akhir' => [StatusTransaksi::Dibatalkan, StatusTransaksi::Diproses],
            'status milik canvassing' => [StatusTransaksi::Diproses, StatusTransaksi::Tervalidasi],
            'batal tanpa alasan' => [StatusTransaksi::Diproses, StatusTransaksi::Dibatalkan],
        ];
    }

    #[DataProvider('transisiTerlarang')]
    public function test_disallowed_transition_is_rejected_without_any_change(StatusTransaksi $statusLama, StatusTransaksi $statusBaru): void
    {
        $pesanan = $this->pesanan($statusLama);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();

        $this->assertDitolakValidasi('status', fn () => (new StatusPesananService)->ubahStatus($pesanan, $statusBaru, $kasir));

        $this->assertSame($statusLama, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_second_click_with_a_stale_page_is_rejected_and_recorded_once(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $service = new StatusPesananService;
        $service->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kasir);

        $this->assertDitolakValidasi('status', fn () => $service->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kasir));

        $this->assertSame(StatusTransaksi::Dijemput, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 1);
    }

    public function test_order_without_courier_cannot_be_picked_up(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['kurir_id' => null]);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();

        $this->assertDitolakValidasi('kurir_id', fn () => (new StatusPesananService)->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kasir));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    /**
     * @return array<string, array{0: Kanal}>
     */
    public static function kanalBukanCod(): array
    {
        return [
            'walk-in' => [Kanal::WalkIn],
            'canvassing' => [Kanal::Canvassing],
        ];
    }

    #[DataProvider('kanalBukanCod')]
    public function test_non_cod_transaction_is_rejected_by_every_action(Kanal $kanal): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['jenis_kanal' => $kanal]);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $kurir = User::factory()->kurir()->for($pesanan->cabang)->create();
        $service = new StatusPesananService;

        $this->assertDitolakValidasi('status', fn () => $service->catatStatusAwal($pesanan, $kasir));
        $this->assertDitolakValidasi('status', fn () => $service->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kasir));
        $this->assertDitolakValidasi('status', fn () => $service->batalkan($pesanan, $kasir, 'Alasan uji'));
        $this->assertDitolakValidasi('status', fn () => $service->tugaskanKurir($pesanan, $kurir, $kasir));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function stafCabangYangBerhak(): array
    {
        return [
            'kasir' => ['kasir'],
            'admin cabang' => ['adminCabang'],
        ];
    }

    #[DataProvider('stafCabangYangBerhak')]
    public function test_branch_staff_can_advance_status_in_own_branch(string $peran): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $staf = User::factory()->{$peran}()->for($pesanan->cabang)->create();

        $hasil = (new StatusPesananService)->ubahStatus($pesanan, StatusTransaksi::Dijemput, $staf);

        $this->assertSame(StatusTransaksi::Dijemput, $hasil->status);
    }

    public function test_assigned_courier_can_advance_status(): void
    {
        $kurir = User::factory()->kurir()->create();
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['cabang_id' => $kurir->cabang_id, 'kurir_id' => $kurir->id]);

        $hasil = (new StatusPesananService)->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kurir);

        $this->assertSame(StatusTransaksi::Dijemput, $hasil->status);
        $this->assertSame($kurir->id, $pesanan->riwayatStatus()->firstOrFail()->diubah_oleh_staff);
    }

    /**
     * @return array<string, array{0: string, 1: bool, 2: array<string, bool>}>
     */
    public static function stafTanpaHak(): array
    {
        return [
            'kasir cabang lain' => ['kasir', false, []],
            'admin cabang lain' => ['adminCabang', false, []],
            'kurir cabang lain' => ['kurir', false, []],
            'kurir cabang sama yang tidak ditugaskan' => ['kurir', true, []],
            'kasir nonaktif' => ['kasir', true, ['is_active' => false]],
            'admin cabang nonaktif' => ['adminCabang', true, ['is_active' => false]],
        ];
    }

    /**
     * @param  array<string, bool>  $atribut
     */
    #[DataProvider('stafTanpaHak')]
    public function test_staff_without_rights_cannot_advance_status(string $peran, bool $cabangSama, array $atribut): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $cabang = $cabangSama ? $pesanan->cabang : Cabang::factory()->create();
        $staf = User::factory()->{$peran}()->for($cabang)->create($atribut);

        $this->assertDitolakOtorisasi(fn () => (new StatusPesananService)->ubahStatus($pesanan, StatusTransaksi::Dijemput, $staf));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_inactive_assigned_courier_cannot_advance_status(): void
    {
        $kurir = User::factory()->kurir()->inactive()->create();
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['cabang_id' => $kurir->cabang_id, 'kurir_id' => $kurir->id]);

        $this->assertDitolakOtorisasi(fn () => (new StatusPesananService)->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kurir));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
    }

    public function test_super_admin_cannot_manage_orders(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kurir = User::factory()->kurir()->for($pesanan->cabang)->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $service = new StatusPesananService;

        $this->assertDitolakOtorisasi(fn () => $service->ubahStatus($pesanan, StatusTransaksi::Dijemput, $superAdmin));
        $this->assertDitolakOtorisasi(fn () => $service->batalkan($pesanan, $superAdmin, 'Alasan uji'));
        $this->assertDitolakOtorisasi(fn () => $service->tugaskanKurir($pesanan, $kurir, $superAdmin));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertNotSame($kurir->id, $pesanan->fresh()?->kurir_id);
    }

    #[DataProvider('stafCabangYangBerhak')]
    public function test_branch_staff_can_cancel_with_reason_stored_in_history(string $peran): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $staf = User::factory()->{$peran}()->for($pesanan->cabang)->create();

        $hasil = (new StatusPesananService)->batalkan($pesanan, $staf, '  Pelanggan membatalkan lewat telepon  ');

        $this->assertSame(StatusTransaksi::Dibatalkan, $hasil->status);

        $riwayat = $pesanan->riwayatStatus()->sole();
        $this->assertSame(StatusTransaksi::Diproses, $riwayat->status_lama);
        $this->assertSame(StatusTransaksi::Dibatalkan, $riwayat->status_baru);
        $this->assertSame('Pelanggan membatalkan lewat telepon', $riwayat->catatan);
        $this->assertSame($staf->id, $riwayat->diubah_oleh_staff);
        $this->assertNull($riwayat->diubah_oleh_pelanggan);
    }

    public function test_customer_can_cancel_own_order(): void
    {
        $pelanggan = Pelanggan::factory()->create();
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['pelanggan_id' => $pelanggan->id]);

        $hasil = (new StatusPesananService)->batalkan($pesanan, $pelanggan, 'Salah alamat');

        $this->assertSame(StatusTransaksi::Dibatalkan, $hasil->status);

        $riwayat = $pesanan->riwayatStatus()->sole();
        $this->assertSame($pelanggan->id, $riwayat->diubah_oleh_pelanggan);
        $this->assertNull($riwayat->diubah_oleh_staff);
    }

    public function test_customer_cannot_cancel_order_of_another_customer(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['pelanggan_id' => Pelanggan::factory()->create()->id]);
        $pelangganLain = Pelanggan::factory()->create();

        $this->assertDitolakOtorisasi(fn () => (new StatusPesananService)->batalkan($pesanan, $pelangganLain, 'Salah alamat'));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_customer_cannot_cancel_manual_order_without_owner(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $pelanggan = Pelanggan::factory()->create();

        $this->assertDitolakOtorisasi(fn () => (new StatusPesananService)->batalkan($pesanan, $pelanggan, 'Salah alamat'));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function stafYangTidakBolehMembatalkan(): array
    {
        return [
            'kurir yang ditugaskan' => ['kurir', true],
            'kasir cabang lain' => ['kasir', false],
            'admin cabang lain' => ['adminCabang', false],
        ];
    }

    #[DataProvider('stafYangTidakBolehMembatalkan')]
    public function test_staff_without_rights_cannot_cancel(string $peran, bool $cabangSama): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $staf = User::factory()->{$peran}()->for($cabangSama ? $pesanan->cabang : Cabang::factory()->create())->create();

        $this->assertDitolakOtorisasi(fn () => (new StatusPesananService)->batalkan($pesanan, $staf, 'Alasan uji'));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    /**
     * @return array<string, array{0: StatusTransaksi}>
     */
    public static function statusYangTidakBisaDibatalkan(): array
    {
        return [
            'dijemput' => [StatusTransaksi::Dijemput],
            'diantar' => [StatusTransaksi::Diantar],
            'selesai' => [StatusTransaksi::Selesai],
            'sudah dibatalkan' => [StatusTransaksi::Dibatalkan],
        ];
    }

    #[DataProvider('statusYangTidakBisaDibatalkan')]
    public function test_order_past_processing_cannot_be_cancelled(StatusTransaksi $status): void
    {
        $pesanan = $this->pesanan($status);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();

        $this->assertDitolakValidasi('status', fn () => (new StatusPesananService)->batalkan($pesanan, $kasir, 'Alasan uji'));

        $this->assertSame($status, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    /**
     * @return array<string, array{0: int}>
     */
    public static function alasanTidakValid(): array
    {
        return [
            'kosong' => [0],
            'lebih dari batas' => [501],
        ];
    }

    #[DataProvider('alasanTidakValid')]
    public function test_cancellation_reason_outside_the_limit_is_rejected(int $panjang): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();

        $this->assertDitolakValidasi('alasan', fn () => (new StatusPesananService)->batalkan($pesanan, $kasir, str_repeat('a', $panjang)));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_whitespace_only_cancellation_reason_is_rejected(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();

        $this->assertDitolakValidasi('alasan', fn () => (new StatusPesananService)->batalkan($pesanan, $kasir, "  \n  "));

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
    }

    public function test_cancellation_reason_at_the_limit_is_accepted(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $alasan = str_repeat('é', 500);

        (new StatusPesananService)->batalkan($pesanan, $kasir, $alasan);

        $this->assertSame($alasan, $pesanan->riwayatStatus()->sole()->catatan);
    }

    public function test_active_courier_of_same_branch_is_assigned_without_adding_history(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['kurir_id' => null]);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $kurir = User::factory()->kurir()->for($pesanan->cabang)->create();

        $hasil = (new StatusPesananService)->tugaskanKurir($pesanan, $kurir, $kasir);

        $this->assertSame($kurir->id, $hasil->kurir_id);
        $this->assertSame($kurir->id, $pesanan->fresh()?->kurir_id);
        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_courier_can_be_replaced_while_processing(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $adminCabang = User::factory()->adminCabang()->for($pesanan->cabang)->create();
        $kurirBaru = User::factory()->kurir()->for($pesanan->cabang)->create();

        (new StatusPesananService)->tugaskanKurir($pesanan, $kurirBaru, $adminCabang);

        $this->assertSame($kurirBaru->id, $pesanan->fresh()?->kurir_id);
    }

    public function test_courier_cannot_be_changed_after_processing(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Dijemput);
        $kurirLama = $pesanan->kurir_id;
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $kurirBaru = User::factory()->kurir()->for($pesanan->cabang)->create();

        $this->assertDitolakValidasi('status', fn () => (new StatusPesananService)->tugaskanKurir($pesanan, $kurirBaru, $kasir));

        $this->assertSame($kurirLama, $pesanan->fresh()?->kurir_id);
    }

    /**
     * @return array<string, array{0: string, 1: bool, 2: array<string, bool>}>
     */
    public static function kurirTidakValid(): array
    {
        return [
            'kurir nonaktif' => ['kurir', true, ['is_active' => false]],
            'kurir cabang lain' => ['kurir', false, []],
            'akun kasir' => ['kasir', true, []],
        ];
    }

    /**
     * @param  array<string, bool>  $atribut
     */
    #[DataProvider('kurirTidakValid')]
    public function test_invalid_courier_choice_is_rejected(string $peran, bool $cabangSama, array $atribut): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['kurir_id' => null]);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $pilihan = User::factory()->{$peran}()->for($cabangSama ? $pesanan->cabang : Cabang::factory()->create())->create($atribut);

        $this->assertDitolakValidasi('kurir_id', fn () => (new StatusPesananService)->tugaskanKurir($pesanan, $pilihan, $kasir));

        $this->assertNull($pesanan->fresh()?->kurir_id);
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function stafYangTidakBolehMenugaskan(): array
    {
        return [
            'kurir cabang sama' => ['kurir', true],
            'kasir cabang lain' => ['kasir', false],
            'admin cabang lain' => ['adminCabang', false],
        ];
    }

    #[DataProvider('stafYangTidakBolehMenugaskan')]
    public function test_staff_without_rights_cannot_assign_courier(string $peran, bool $cabangSama): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['kurir_id' => null]);
        $kurir = User::factory()->kurir()->for($pesanan->cabang)->create();
        $staf = User::factory()->{$peran}()->for($cabangSama ? $pesanan->cabang : Cabang::factory()->create())->create();

        $this->assertDitolakOtorisasi(fn () => (new StatusPesananService)->tugaskanKurir($pesanan, $kurir, $staf));

        $this->assertNull($pesanan->fresh()?->kurir_id);
    }

    public function test_initial_status_of_manual_order_is_recorded_for_staff(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['kurir_id' => null]);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();

        $riwayat = (new StatusPesananService)->catatStatusAwal($pesanan, $kasir);

        $this->assertNull($riwayat->status_lama);
        $this->assertSame(StatusTransaksi::Diproses, $riwayat->status_baru);
        $this->assertSame($kasir->id, $riwayat->diubah_oleh_staff);
        $this->assertNull($riwayat->diubah_oleh_pelanggan);
        $this->assertModelExists($riwayat);
    }

    public function test_initial_status_of_online_order_is_recorded_for_customer(): void
    {
        $pelanggan = Pelanggan::factory()->create();
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['pelanggan_id' => $pelanggan->id, 'kurir_id' => null, 'dicatat_oleh' => null]);

        $riwayat = (new StatusPesananService)->catatStatusAwal($pesanan, $pelanggan);

        $this->assertNull($riwayat->status_lama);
        $this->assertSame($pelanggan->id, $riwayat->diubah_oleh_pelanggan);
        $this->assertNull($riwayat->diubah_oleh_staff);
    }

    public function test_initial_status_is_recorded_only_once(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        $service = new StatusPesananService;
        $service->catatStatusAwal($pesanan, $kasir);

        $this->assertDitolakValidasi('status', fn () => $service->catatStatusAwal($pesanan, $kasir));

        $this->assertDatabaseCount('riwayat_status', 1);
    }

    public function test_initial_status_is_rejected_for_order_past_processing(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Dijemput);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();

        $this->assertDitolakValidasi('status', fn () => (new StatusPesananService)->catatStatusAwal($pesanan, $kasir));

        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_initial_status_cannot_be_recorded_by_outsiders(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses, ['pelanggan_id' => Pelanggan::factory()->create()->id]);
        $service = new StatusPesananService;
        $pelangganLain = Pelanggan::factory()->create();
        $kasirCabangLain = User::factory()->kasir()->create();

        $this->assertDitolakOtorisasi(fn () => $service->catatStatusAwal($pesanan, $pelangganLain));
        $this->assertDitolakOtorisasi(fn () => $service->catatStatusAwal($pesanan, $kasirCabangLain));

        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_status_change_is_rolled_back_when_history_cannot_be_saved(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Diproses);
        $kasir = User::factory()->kasir()->for($pesanan->cabang)->create();
        RiwayatStatus::creating(function (): never {
            throw new RuntimeException('Riwayat gagal disimpan');
        });

        $this->assertThrows(
            fn () => (new StatusPesananService)->ubahStatus($pesanan, StatusTransaksi::Dijemput, $kasir),
            RuntimeException::class,
        );

        $this->assertSame(StatusTransaksi::Diproses, $pesanan->fresh()?->status);
        $this->assertDatabaseCount('riwayat_status', 0);
    }

    public function test_order_history_is_returned_from_the_earliest(): void
    {
        $pesanan = $this->pesanan(StatusTransaksi::Dijemput);
        $pertama = RiwayatStatus::factory()->for($pesanan)->create();
        $kedua = RiwayatStatus::factory()->for($pesanan)->create(['status_lama' => StatusTransaksi::Diproses, 'status_baru' => StatusTransaksi::Dijemput]);
        RiwayatStatus::factory()->create();

        $this->assertSame([$pertama->id, $kedua->id], $pesanan->riwayatStatus()->pluck('id')->all());
    }

    public function test_order_with_history_cannot_be_deleted(): void
    {
        $riwayat = RiwayatStatus::factory()->create();

        $this->assertThrows(fn () => $riwayat->transaksi->delete(), QueryException::class);

        $this->assertModelExists($riwayat->transaksi);
        $this->assertModelExists($riwayat);
    }

    /**
     * @param  array<string, mixed>  $atribut
     */
    private function pesanan(StatusTransaksi $status, array $atribut = []): Transaksi
    {
        return Transaksi::factory()->cod($status)->create($atribut);
    }

    private function assertDitolakValidasi(string $kolom, callable $aksi): void
    {
        $this->assertThrows($aksi, function (ValidationException $e) use ($kolom): bool {
            $this->assertArrayHasKey($kolom, $e->errors());

            return true;
        });
    }

    private function assertDitolakOtorisasi(callable $aksi): void
    {
        $this->assertThrows($aksi, AuthorizationException::class, 'Anda tidak berhak mengelola pesanan ini.');
    }
}
