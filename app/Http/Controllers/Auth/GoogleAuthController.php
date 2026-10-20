<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
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
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            $driver = Socialite::driver('google');

            if (! $driver instanceof AbstractProvider) {
                throw new InvalidStateException('Driver does not support stateless OAuth.');
            }

            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = $driver->stateless()->user();
        } catch (Throwable $e) {
            Log::error('Google OAuth callback error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('customer.login')->withErrors([
                'phone' => __('Gagal masuk dengan akun Google (:error). Silakan coba lagi.', ['error' => $e->getMessage() ?: class_basename($e)]),
            ]);
        }

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();

        // 1. Cari akun hanya lewat google_id (jangan auto-link via email)
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            if (! $user->is_active) {
                return redirect()->route('customer.login')->withErrors([
                    'phone' => __('Akun Anda tidak aktif. Silakan hubungi admin.'),
                ]);
            }

            if (! $user->isPelanggan()) {
                return redirect()->route('customer.login')->withErrors([
                    'phone' => __('Akun staf tidak dapat masuk di sini. Silakan masuk melalui portal staf di /login.'),
                ]);
            }

            // Perbarui foto profil Google jika berubah
            $avatarUrl = $googleUser->getAvatar();
            if ($avatarUrl && $user->google_avatar !== $avatarUrl) {
                $user->google_avatar = $avatarUrl;
                $user->save();
            }

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        // 2. Jika akun belum ada, cek apakah email Google sudah digunakan akun lain
        if ($email && User::where('email', $email)->exists()) {
            return redirect()->route('customer.login')->withErrors([
                'phone' => __('Email dari akun Google ini sudah digunakan oleh akun lain.'),
            ]);
        }

        // 3. Buat akun pelanggan baru via Google
        $newUser = new User;
        $newUser->name = $googleUser->getName() ?: 'Pelanggan';
        $newUser->email = $email;
        $newUser->google_id = $googleId;
        $newUser->google_avatar = $googleUser->getAvatar();
        $newUser->role = Role::Pelanggan;
        $newUser->is_active = true;
        $newUser->cabang_id = null;
        $newUser->save();

        Auth::guard('web')->login($newUser);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('Berhasil masuk dengan akun Google!'));
    }
}
