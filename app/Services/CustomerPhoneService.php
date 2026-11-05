<?php

namespace App\Services;

use App\Models\Pelanggan;
use Illuminate\Validation\ValidationException;

class CustomerPhoneService
{
    /**
     * Pesan penolakan resmi sesuai PRD v1.2 §6 dan Sprint 2 Backlog US-08.
     */
    public const DUPLICATE_PHONE_MESSAGE = 'Nomor sudah terdaftar, silakan masuk dengan nomor HP atau hubungi depot.';

    /**
     * Standarisasi nomor telepon Indonesia (e.g., 0812 -> 62812, +62812 -> 62812, 812 -> 62812).
     */
    public function normalize(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Periksa apakah nomor HP sudah terdaftar pada pelanggan lain.
     */
    public function isPhoneRegistered(string $normalizedPhone, ?int $excludePelangganId = null): bool
    {
        $query = Pelanggan::query()->where('no_hp', $normalizedPhone);

        if ($excludePelangganId !== null) {
            $query->where('id', '!=', $excludePelangganId);
        }

        return $query->exists();
    }

    /**
     * Validasi keunikan nomor HP dan lemparkan ValidationException jika sudah terdaftar.
     *
     * @throws ValidationException
     */
    public function ensurePhoneIsUnique(string $normalizedPhone, ?int $excludePelangganId = null): void
    {
        if ($this->isPhoneRegistered($normalizedPhone, $excludePelangganId)) {
            throw ValidationException::withMessages([
                'phone' => [__(self::DUPLICATE_PHONE_MESSAGE)],
            ]);
        }
    }
}

