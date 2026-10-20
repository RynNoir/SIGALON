<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case AdminCabang = 'admin_cabang';
    case Kasir = 'kasir';
    case Kurir = 'kurir';
    case Pelanggan = 'pelanggan';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::AdminCabang => 'Admin Cabang',
            self::Kasir => 'Kasir',
            self::Kurir => 'Kurir',
            self::Pelanggan => 'Pelanggan',
        };
    }

    /**
     * Super Admin bekerja lintas cabang; pelanggan tidak terikat cabang; peran staf lain wajib terikat pada satu cabang.
     */
    public function requiresCabang(): bool
    {
        return ! in_array($this, [self::SuperAdmin, self::Pelanggan], true);
    }

    /**
     * Kasir dan Kurir bekerja lewat aplikasi Android (PRD §4); pelanggan memakai portal pelanggan; website admin hanya untuk Super Admin dan Admin Cabang.
     */
    public function canAccessWeb(): bool
    {
        return in_array($this, [self::SuperAdmin, self::AdminCabang], true);
    }

    /**
     * @return array<int, self>
     */
    public static function staffCases(): array
    {
        return [self::SuperAdmin, self::AdminCabang, self::Kasir, self::Kurir];
    }
}
