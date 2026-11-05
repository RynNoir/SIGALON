<?php

namespace App\Services;

use App\Enums\Kanal;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
use App\Models\Pelanggan;
use App\Models\RiwayatStatus;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Satu-satunya pintu perubahan status dan penugasan kurir pesanan COD (PRD §6, ERD v1.1).
 *
 * Otorisasi diperiksa di sini agar berlaku sama untuk website dan API Android.
 * Penolakan berupa AuthorizationException (403) atau ValidationException (422).
 * Method mengembalikan model terbaru; instance yang dikirim pemanggil tidak ikut diperbarui.
 */
class StatusPesananService
{
    private const PANJANG_MAKSIMAL_ALASAN = 500;

    /**
     * Catat status awal pesanan yang baru dibuat. Panggil dalam satu DB::transaction dengan penyimpanan pesanan.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function catatStatusAwal(Transaksi $pesanan, User|Pelanggan $pengubah): RiwayatStatus
    {
        return DB::transaction(function () use ($pesanan, $pengubah): RiwayatStatus {
            $terkunci = $this->kunci($pesanan);

            $this->pastikanStafCabangAtauPemilik($terkunci, $pengubah);
            $this->pastikanCod($terkunci);

            if ($terkunci->status !== StatusTransaksi::Diproses || $terkunci->riwayatStatus()->exists()) {
                throw ValidationException::withMessages([
                    'status' => __('Status awal hanya dicatat satu kali untuk pesanan yang baru dibuat.'),
                ]);
            }

            return $this->catatRiwayat($terkunci, null, $pengubah);
        });
    }

    /**
     * Majukan status ke tujuan yang disebutkan, sehingga klik ganda ditolak dan tidak melompati status.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function ubahStatus(Transaksi $pesanan, StatusTransaksi $statusBaru, User $pengubah): Transaksi
    {
        return DB::transaction(function () use ($pesanan, $statusBaru, $pengubah): Transaksi {
            $terkunci = $this->kunci($pesanan);

            $this->pastikanBolehMemajukan($terkunci, $pengubah);
            $this->pastikanCod($terkunci);

            // Pembatalan wajib lewat batalkan() karena harus disertai alasan.
            if ($statusBaru === StatusTransaksi::Dibatalkan || ! $terkunci->status->bolehBerubahKe($statusBaru)) {
                throw ValidationException::withMessages([
                    'status' => __('Status pesanan tidak bisa diubah dari :lama ke :baru.', [
                        'lama' => $terkunci->status->label(),
                        'baru' => $statusBaru->label(),
                    ]),
                ]);
            }

            if ($statusBaru === StatusTransaksi::Dijemput && $terkunci->kurir_id === null) {
                throw ValidationException::withMessages([
                    'kurir_id' => __('Tugaskan kurir sebelum pesanan dijemput.'),
                ]);
            }

            return $this->terapkanStatus($terkunci, $statusBaru, $pengubah);
        });
    }

    /**
     * Batalkan pesanan yang masih diproses, oleh Kasir/Admin Cabang di cabangnya atau pelanggan pemilik pesanan.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function batalkan(Transaksi $pesanan, User|Pelanggan $pengubah, string $alasan): Transaksi
    {
        $alasan = trim($alasan);

        return DB::transaction(function () use ($pesanan, $pengubah, $alasan): Transaksi {
            $terkunci = $this->kunci($pesanan);

            $this->pastikanStafCabangAtauPemilik($terkunci, $pengubah);
            $this->pastikanCod($terkunci);

            if (! $terkunci->status->bolehBerubahKe(StatusTransaksi::Dibatalkan)) {
                throw ValidationException::withMessages([
                    'status' => __('Pesanan hanya bisa dibatalkan selama statusnya masih Diproses.'),
                ]);
            }

            if ($alasan === '' || mb_strlen($alasan) > self::PANJANG_MAKSIMAL_ALASAN) {
                throw ValidationException::withMessages([
                    'alasan' => __('Alasan pembatalan wajib diisi, maksimal :maks karakter.', [
                        'maks' => self::PANJANG_MAKSIMAL_ALASAN,
                    ]),
                ]);
            }

            return $this->terapkanStatus($terkunci, StatusTransaksi::Dibatalkan, $pengubah, $alasan);
        });
    }

    /**
     * Tugaskan atau ganti kurir selama pesanan masih diproses. Bukan perubahan status, jadi tidak menambah riwayat.
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function tugaskanKurir(Transaksi $pesanan, User $kurir, User $pengubah): Transaksi
    {
        return DB::transaction(function () use ($pesanan, $kurir, $pengubah): Transaksi {
            $terkunci = $this->kunci($pesanan);

            $this->pastikanStafCabang($terkunci, $pengubah);
            $this->pastikanCod($terkunci);

            if ($terkunci->status !== StatusTransaksi::Diproses) {
                throw ValidationException::withMessages([
                    'status' => __('Kurir hanya bisa ditugaskan selama pesanan masih Diproses.'),
                ]);
            }

            if ($kurir->role !== Role::Kurir || ! $kurir->is_active || $kurir->cabang_id !== $terkunci->cabang_id) {
                throw ValidationException::withMessages([
                    'kurir_id' => __('Pilih kurir aktif dari cabang yang sama dengan pesanan.'),
                ]);
            }

            $terkunci->kurir_id = $kurir->id;
            $terkunci->save();

            return $terkunci;
        });
    }

    /**
     * Baca ulang pesanan dengan kunci baris agar dua staf tidak mengubahnya bersamaan.
     */
    private function kunci(Transaksi $pesanan): Transaksi
    {
        return Transaksi::query()->lockForUpdate()->findOrFail($pesanan->id);
    }

    private function terapkanStatus(Transaksi $pesanan, StatusTransaksi $statusBaru, User|Pelanggan $pengubah, ?string $catatan = null): Transaksi
    {
        $statusLama = $pesanan->status;

        $pesanan->status = $statusBaru;
        $pesanan->save();

        $this->catatRiwayat($pesanan, $statusLama, $pengubah, $catatan);

        return $pesanan;
    }

    private function catatRiwayat(Transaksi $pesanan, ?StatusTransaksi $statusLama, User|Pelanggan $pengubah, ?string $catatan = null): RiwayatStatus
    {
        $riwayat = new RiwayatStatus;
        $riwayat->transaksi_id = $pesanan->id;
        $riwayat->status_lama = $statusLama;
        $riwayat->status_baru = $pesanan->status;
        $riwayat->diubah_oleh_staff = $pengubah instanceof User ? $pengubah->id : null;
        $riwayat->diubah_oleh_pelanggan = $pengubah instanceof Pelanggan ? $pengubah->id : null;
        $riwayat->catatan = $catatan;
        $riwayat->save();

        return $riwayat;
    }

    /**
     * @throws ValidationException
     */
    private function pastikanCod(Transaksi $pesanan): void
    {
        if ($pesanan->jenis_kanal !== Kanal::Cod) {
            throw ValidationException::withMessages([
                'status' => __('Transaksi ini bukan pesanan COD.'),
            ]);
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function pastikanBolehMemajukan(Transaksi $pesanan, User $pengubah): void
    {
        if ($pengubah->role !== Role::Kurir) {
            $this->pastikanStafCabang($pesanan, $pengubah);

            return;
        }

        if (! $pengubah->is_active || $pesanan->kurir_id !== $pengubah->id) {
            throw $this->aksesDitolak();
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function pastikanStafCabangAtauPemilik(Transaksi $pesanan, User|Pelanggan $pengubah): void
    {
        if ($pengubah instanceof User) {
            $this->pastikanStafCabang($pesanan, $pengubah);

            return;
        }

        if ($pesanan->pelanggan_id !== $pengubah->id) {
            throw $this->aksesDitolak();
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function pastikanStafCabang(Transaksi $pesanan, User $staf): void
    {
        $berhak = in_array($staf->role, [Role::Kasir, Role::AdminCabang], true)
            && $staf->is_active
            && $staf->cabang_id === $pesanan->cabang_id;

        if (! $berhak) {
            throw $this->aksesDitolak();
        }
    }

    private function aksesDitolak(): AuthorizationException
    {
        return new AuthorizationException(__('Anda tidak berhak mengelola pesanan ini.'));
    }
}
