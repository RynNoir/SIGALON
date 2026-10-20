<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Contracts\View\View;

class LandingPageController extends Controller
{
    /**
     * Tampilkan landing page SIGALON beserta data titik cabang untuk peta Leaflet.
     */
    public function __invoke(): View
    {
        $branches = Cabang::query()
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('nama')
            ->get(['id', 'nama', 'alamat', 'latitude', 'longitude']);

        $hargaWalkIn = (int) config('sigalon.harga.walk_in', 6000);
        $hargaCod = (int) config('sigalon.harga.cod', 7000);
        $hargaGalonBaru = config('sigalon.harga.galon_baru');

        $adminPhone = (string) config('services.admin_whatsapp_number', '6281200000000');
        $cleanPhone = preg_replace('/\D+/', '', $adminPhone);
        $whatsappMessage = rawurlencode('Halo CV. Aufa, saya ingin memesan air galon SIGALON.');
        $whatsappUrl = "https://wa.me/{$cleanPhone}?text={$whatsappMessage}";

        $whatsappGalonBaruMessage = rawurlencode('Halo CV. Aufa, saya ingin menanyakan ketersediaan stok galon kosong baru di depot.');
        $whatsappGalonBaruUrl = "https://wa.me/{$cleanPhone}?text={$whatsappGalonBaruMessage}";

        return view('welcome', [
            'branches' => $branches,
            'hargaWalkIn' => $hargaWalkIn,
            'hargaCod' => $hargaCod,
            'hargaGalonBaru' => $hargaGalonBaru !== null ? (int) $hargaGalonBaru : null,
            'whatsappUrl' => $whatsappUrl,
            'whatsappGalonBaruUrl' => $whatsappGalonBaruUrl,
        ]);
    }
}
