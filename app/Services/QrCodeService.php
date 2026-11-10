<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Daftar nama file kandidat QR statis di storage disk public.
     *
     * @var list<string>
     */
    public const CANDIDATE_PATHS = [
        'qr/qr-statis.png',
        'qr/qr-statis.jpg',
        'qr/qr-statis.jpeg',
        'qr/qr-statis.webp',
        'qr/qris-statis.png',
        'qr/qris-statis.jpg',
        'qr/qris-statis.jpeg',
        'qr/qris-statis.webp',
    ];

    /**
     * Mengembalikan path relatif file QR statis pada disk public jika tersedia.
     */
    public function getQrCodePath(): ?string
    {
        $disk = Storage::disk('public');

        foreach (self::CANDIDATE_PATHS as $path) {
            if ($disk->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Memeriksa apakah file gambar QR statis sudah tersedia di storage disk public.
     */
    public function hasQrCode(): bool
    {
        return $this->getQrCodePath() !== null;
    }

    /**
     * Mengembalikan URL publik gambar QR statis jika tersedia, atau null jika belum diunggah.
     */
    public function getQrCodeUrl(): ?string
    {
        $path = $this->getQrCodePath();

        if ($path === null) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
