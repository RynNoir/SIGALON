<?php

namespace App\Enums;

/**
 * Status TRANSAKSI per kanal (ERD v1.1, "Aturan nilai kolom"):
 * walk-in langsung selesai; COD diproses → dijemput → diantar → selesai (atau dibatalkan dari diproses);
 * canvassing menunggu_verifikasi → tervalidasi / ditolak.
 */
enum StatusTransaksi: string
{
    case Diproses = 'diproses';
    case Dijemput = 'dijemput';
    case Diantar = 'diantar';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Tervalidasi = 'tervalidasi';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Diproses => 'Diproses',
            self::Dijemput => 'Dijemput',
            self::Diantar => 'Diantar',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
            self::MenungguVerifikasi => 'Menunggu verifikasi',
            self::Tervalidasi => 'Tervalidasi',
            self::Ditolak => 'Ditolak',
        };
    }

    /**
     * Status yang dihitung sebagai pendapatan dan transaksi sah (PRD §6): walk-in/COD selesai, canvassing tervalidasi.
     *
     * @return list<self>
     */
    public static function dihitungDiLaporan(): array
    {
        return [self::Selesai, self::Tervalidasi];
    }
}
