<?php

namespace App\Enums;

enum JenisItem: string
{
    case IsiUlang = 'isi_ulang';
    case GalonBaru = 'galon_baru';

    public function label(): string
    {
        return match ($this) {
            self::IsiUlang => 'Air isi ulang',
            self::GalonBaru => 'Galon kosong baru',
        };
    }
}
