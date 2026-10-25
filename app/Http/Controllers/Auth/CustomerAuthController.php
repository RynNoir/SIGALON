<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    /**
     * Tampilkan formulir masuk dan daftar pelanggan.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('pelanggan')->check()) {
            return redirect()->route('home');
        }

        $adminWhatsapp = (string) config('services.admin_whatsapp_number', '6281200000000');
        $helpMessage = 'Halo Admin SIGALON, saya butuh bantuan terkait kata sandi akun pelanggan saya.';
        $whatsappUrl = 'https://wa.me/'.$adminWhatsapp.'?text='.rawurlencode($helpMessage);

        return view('pages.auth.customer-login', [
            'whatsappUrl' => $whatsappUrl,
        ]);
    }

    /**
     * Proses masuk pelanggan dengan nomor HP dan kata sandi.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'phone.required' => __('Nomor WhatsApp / HP wajib diisi.'),
            'password.required' => __('Kata sandi wajib diisi.'),
        ]);

        $normalizedPhone = $this->normalizePhoneNumber($request->string('phone')->toString());
        $throttleKey = Str::transliterate('customer_login|'.$normalizedPhone.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            abort(429, __('Terlalu banyak percobaan masuk. Silakan coba lagi dalam :seconds detik.', ['seconds' => $seconds]));
        }

        $pelanggan = Pelanggan::where('no_hp', $normalizedPhone)->first();

        if (! $pelanggan || ! Hash::check($request->string('password')->toString(), (string) $pelanggan->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'phone' => [__('Nomor HP atau kata sandi salah.')],
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('pelanggan')->login($pelanggan, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Pendaftaran akun pelanggan baru melalui nomor HP.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => __('Nama lengkap wajib diisi.'),
            'phone.required' => __('Nomor WhatsApp / HP wajib diisi.'),
            'phone.min' => __('Nomor HP minimal terdiri dari 9 digit.'),
            'password.required' => __('Kata sandi wajib diisi.'),
            'password.min' => __('Kata sandi minimal 8 karakter.'),
            'password.confirmed' => __('Konfirmasi kata sandi tidak cocok.'),
        ]);

        $normalizedPhone = $this->normalizePhoneNumber($request->string('phone')->toString());

        if (Pelanggan::where('no_hp', $normalizedPhone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => [__('Nomor sudah terdaftar, silakan masuk dengan nomor HP atau hubungi depot.')],
            ]);
        }

        $pelanggan = new Pelanggan;
        $pelanggan->nama = $request->string('name')->toString();
        $pelanggan->no_hp = $normalizedPhone;
        $pelanggan->password = $request->string('password')->toString();
        $pelanggan->save();

        Auth::guard('pelanggan')->login($pelanggan);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('Pendaftaran berhasil. Selamat datang di SIGALON!'));
    }

    /**
     * Keluar dari sesi pelanggan.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('pelanggan')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Standarisasi nomor telepon Indonesia (e.g., 0812 -> 62812, +62812 -> 62812).
     */
    private function normalizePhoneNumber(string $phone): string
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
}
