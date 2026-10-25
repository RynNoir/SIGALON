<?php

namespace App\Models;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\StatusTransaksi;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\TransaksiFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * ERD v1.1: TRANSAKSI. Satu baris berisi satu jenis item.
 *
 * @property int $id
 * @property int $cabang_id
 * @property int|null $pelanggan_id
 * @property int|null $kurir_id
 * @property Kanal $jenis_kanal
 * @property JenisItem $jenis_item
 * @property int $jumlah_galon
 * @property int $total_harga
 * @property MetodePembayaran $metode_bayar
 * @property StatusTransaksi $status
 * @property CarbonImmutable $tanggal
 * @property string|null $nama_pemesan
 * @property string|null $no_hp_pemesan
 * @property string|null $alamat_antar
 * @property int|null $dicatat_oleh
 * @property int|null $diverifikasi_oleh
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Table('transaksi')]
class Transaksi extends Model
{
    /** @use HasFactory<TransaksiFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_kanal' => Kanal::class,
            'jenis_item' => JenisItem::class,
            'jumlah_galon' => 'integer',
            'total_harga' => 'integer',
            'metode_bayar' => MetodePembayaran::class,
            'status' => StatusTransaksi::class,
            'tanggal' => 'date',
        ];
    }

    /**
     * Hanya transaksi yang dihitung di dashboard dan laporan (PRD §6).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function sah(Builder $query): void
    {
        $query->whereIn('status', StatusTransaksi::dihitungDiLaporan());
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function antaraTanggal(Builder $query, CarbonInterface $dari, CarbonInterface $sampai): void
    {
        // Batas awal/akhir hari agar benar baik di kolom DATE (MariaDB) maupun nilai tanggal-jam (SQLite di tes).
        $query->whereBetween('tanggal', [
            $dari->copy()->startOfDay()->toDateTimeString(),
            $sampai->copy()->endOfDay()->toDateTimeString(),
        ]);
    }

    /**
     * Agregat transaksi sah per cabang dan per kanal dalam rentang tanggal (dashboard US-04/US-05, laporan).
     * Isi `$cabangId` untuk membatasi ke satu cabang; null berarti lintas cabang (khusus Super Admin).
     *
     * @return Collection<int, array{cabang_id: int, jenis_kanal: Kanal, jumlah_transaksi: int, pendapatan: int}>
     */
    public static function ringkasanPerCabangDanKanal(CarbonInterface $dari, CarbonInterface $sampai, ?int $cabangId = null): Collection
    {
        return static::query()
            ->sah()
            ->antaraTanggal($dari, $sampai)
            ->when($cabangId !== null, fn (Builder $query) => $query->where('cabang_id', $cabangId))
            ->toBase()
            ->selectRaw('cabang_id, jenis_kanal, COUNT(*) as jumlah_transaksi, COALESCE(SUM(total_harga), 0) as pendapatan')
            ->groupBy('cabang_id', 'jenis_kanal')
            ->get()
            ->map(fn (object $baris): array => [
                'cabang_id' => (int) $baris->cabang_id,
                'jenis_kanal' => Kanal::from($baris->jenis_kanal),
                'jumlah_transaksi' => (int) $baris->jumlah_transaksi,
                'pendapatan' => (int) $baris->pendapatan,
            ])
            ->values();
    }

    /**
     * @return BelongsTo<Cabang, $this>
     */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function kurir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kurir_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
