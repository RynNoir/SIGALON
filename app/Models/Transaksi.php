<?php

namespace App\Models;

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use Carbon\CarbonImmutable;
use Database\Factories\TransaksiFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cabang_id
 * @property int $dicatat_oleh
 * @property Kanal $kanal
 * @property JenisItem $jenis_item
 * @property int $jumlah
 * @property int $harga_satuan
 * @property int $total
 * @property MetodePembayaran $metode_pembayaran
 * @property CarbonImmutable|null $dibayar_pada
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
            'kanal' => Kanal::class,
            'jenis_item' => JenisItem::class,
            'jumlah' => 'integer',
            'harga_satuan' => 'integer',
            'total' => 'integer',
            'metode_pembayaran' => MetodePembayaran::class,
            'dibayar_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Cabang, $this>
     */
    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
