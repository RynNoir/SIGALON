<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Alihkan pengguna ke halaman login Google.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani callback dari Google OAuth.
     *
     * Parameter `state` OAuth selalu diperiksa (tanpa mode stateless) untuk mencegah login CSRF.
     * Detail error teknis hanya dicatat di log, tidak ditampilkan ke pelanggan.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            /** @var User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return $this->failed(__('Sesi masuk Google sudah kedaluwarsa. Silakan coba lagi.'));
        } catch (Throwable $e) {
            report($e);

            return $this->failed(__('Gagal masuk dengan akun Google. Silakan coba lagi.'));
        }

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();

        // Cari akun hanya lewat google_id; akun Google dan akun nomor HP tidak digabung otomatis (PRD §6).
        $pelanggan = Pelanggan::where('google_id', $googleId)->first();

        if ($pelanggan) {
            $avatarUrl = $googleUser->getAvatar();

            if ($avatarUrl && $pelanggan->google_avatar !== $avatarUrl) {
                $pelanggan->google_avatar = $avatarUrl;
                $pelanggan->save();
            }

            Auth::guard('pelanggan')->login($pelanggan);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        if ($email && Pelanggan::where('email', $email)->exists()) {
            return $this->failed(__('Email dari akun Google ini sudah terdaftar pada akun pelanggan lain.'));
        }

        $pelanggan = new Pelanggan;
        $pelanggan->nama = $googleUser->getName() ?: 'Pelanggan';
        $pelanggan->email = $email;
        $pelanggan->google_id = $googleId;
        $pelanggan->google_avatar = $googleUser->getAvatar();
        $pelanggan->save();

        Auth::guard('pelanggan')->login($pelanggan);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('Berhasil masuk dengan akun Google!'));
    }

    private function failed(string $message): RedirectResponse
    {
        return redirect()->route('customer.login')->withErrors(['phone' => $message]);
    }
}
