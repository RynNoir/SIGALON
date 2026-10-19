<?php

namespace App\Enums;

enum JenisItem: string
{
    case AirIsiUlang = 'air_isi_ulang';
    case GalonKosong = 'galon_kosong';

    public function label(): string
    {
        return match ($this) {
            self::AirIsiUlang => 'Air isi ulang',
            self::GalonKosong => 'Galon kosong baru',
        };
    }
}
