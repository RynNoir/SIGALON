<?php

namespace App\Enums;

enum MetodePembayaran: string
{
    case Tunai = 'tunai';
    case Qr = 'qr';

    public function label(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Qr => 'QR statis',
        };
    }
}
