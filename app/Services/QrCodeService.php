<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

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
     * Parameter versi mencegah browser menampilkan gambar lama setelah QR diganti.
     */
    public function getQrCodeUrl(): ?string
    {
        $path = $this->getQrCodePath();

        if ($path === null) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->url($path).'?v='.$disk->lastModified($path);
    }

    /**
     * Simpan gambar QR baru (US-14 task 17) lalu hapus file QR lama dengan ekstensi lain,
     * supaya hanya satu gambar yang dipakai halaman pemesanan.
     */
    public function replaceQrCode(UploadedFile $file): string
    {
        $extension = strtolower($file->extension());
        $fileName = 'qr-statis.'.(in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true) ? $extension : 'png');

        $path = $file->storeAs('qr', $fileName, 'public');

        if ($path === false) {
            throw new RuntimeException('Gambar QR gagal disimpan.');
        }

        $disk = Storage::disk('public');

        foreach (self::CANDIDATE_PATHS as $candidate) {
            if ($candidate !== $path) {
                $disk->delete($candidate);
            }
        }

        return $path;
    }
}
