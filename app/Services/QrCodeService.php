<?php

namespace App\Services;

use App\Models\Cabang;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Gambar QR statis per cabang (keputusan PO 10 November 2026), disimpan di disk `public`
 * sebagai `qr/cabang-{id}.{ekstensi}`. Tidak ada QR bersama sebagai cadangan, supaya
 * pembayaran tidak masuk ke akun QRIS cabang lain.
 */
class QrCodeService
{
    /**
     * Ekstensi yang diterima, berurutan menurut prioritas bila lebih dari satu file ada.
     *
     * @var list<string>
     */
    public const EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

    /**
     * Mengembalikan path relatif file QR cabang pada disk public jika tersedia.
     */
    public function getQrCodePath(Cabang $cabang): ?string
    {
        $disk = Storage::disk('public');

        foreach ($this->candidatePaths($cabang) as $path) {
            if ($disk->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Memeriksa apakah cabang sudah memiliki gambar QR.
     */
    public function hasQrCode(Cabang $cabang): bool
    {
        return $this->getQrCodePath($cabang) !== null;
    }

    /**
     * Mengembalikan URL publik QR cabang, atau null jika belum diunggah.
     * Parameter versi mencegah browser menampilkan gambar lama setelah QR diganti.
     */
    public function getQrCodeUrl(Cabang $cabang): ?string
    {
        $path = $this->getQrCodePath($cabang);

        if ($path === null) {
            return null;
        }

        $disk = Storage::disk('public');

        return $disk->url($path).'?v='.$disk->lastModified($path);
    }

    /**
     * Simpan gambar QR baru untuk satu cabang, lalu hapus file QR lama cabang itu yang berekstensi lain.
     * QR cabang lain tidak tersentuh.
     */
    public function replaceQrCode(Cabang $cabang, UploadedFile $file): string
    {
        $extension = strtolower($file->extension());
        $fileName = 'cabang-'.$cabang->id.'.'.(in_array($extension, self::EXTENSIONS, true) ? $extension : 'png');

        $path = $file->storeAs('qr', $fileName, 'public');

        if ($path === false) {
            throw new RuntimeException('Gambar QR gagal disimpan.');
        }

        $disk = Storage::disk('public');

        foreach ($this->candidatePaths($cabang) as $candidate) {
            if ($candidate !== $path) {
                $disk->delete($candidate);
            }
        }

        return $path;
    }

    /**
     * @return list<string>
     */
    private function candidatePaths(Cabang $cabang): array
    {
        return array_map(
            fn (string $extension): string => 'qr/cabang-'.$cabang->id.'.'.$extension,
            self::EXTENSIONS,
        );
    }
}
