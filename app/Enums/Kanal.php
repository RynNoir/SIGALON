<?php

namespace App\Enums;

enum Kanal: string
{
    case WalkIn = 'walk_in';
    case Cod = 'cod';
    case Canvassing = 'canvassing';

    public function label(): string
    {
        return match ($this) {
            self::WalkIn => 'Walk-in',
            self::Cod => 'COD/Antar-jemput',
            self::Canvassing => 'Canvassing',
        };
    }
}
