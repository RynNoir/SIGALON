<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case AdminCabang = 'admin_cabang';
    case Kasir = 'kasir';
    case Kurir = 'kurir';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::AdminCabang => 'Admin Cabang',
            self::Kasir => 'Kasir',
            self::Kurir => 'Kurir',
        };
    }

    /**
     * Super Admin bekerja lintas cabang; peran lain wajib terikat pada satu cabang.
     */
    public function requiresCabang(): bool
    {
        return $this !== self::SuperAdmin;
    }

    /**
     * Kasir dan Kurir bekerja lewat aplikasi Android (PRD §4); website admin hanya untuk peran ini.
     */
    public function canAccessWeb(): bool
    {
        return in_array($this, [self::SuperAdmin, self::AdminCabang], true);
    }
}
