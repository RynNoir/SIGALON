<?php

namespace App\Models;

use App\Enums\StatusTransaksi;
use Carbon\CarbonImmutable;
use Database\Factories\RiwayatStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ERD v1.1: RIWAYAT_STATUS. Satu baris per perubahan status; `status_lama` kosong untuk status awal.
 * Tepat satu dari `diubah_oleh_staff` atau `diubah_oleh_pelanggan` terisi.
 *
 * @property int $id
 * @property int $transaksi_id
 * @property StatusTransaksi|null $status_lama
 * @property StatusTransaksi $status_baru
 * @property int|null $diubah_oleh_staff
 * @property int|null $diubah_oleh_pelanggan
 * @property string|null $catatan
 * @property CarbonImmutable $created_at
 */
#[Table('riwayat_status')]
class RiwayatStatus extends Model
{
    /** @use HasFactory<RiwayatStatusFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_lama' => StatusTransaksi::class,
            'status_baru' => StatusTransaksi::class,
        ];
    }

    /**
     * @return BelongsTo<Transaksi, $this>
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function staf(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh_staff');
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'diubah_oleh_pelanggan');
    }
}
