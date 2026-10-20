<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengeluarkan sesi staf yang tidak lagi berhak memakai website admin
 * (dinonaktifkan, peran Kasir/Kurir, atau Admin Cabang tanpa cabang).
 */
class EnsureStaffCanAccessWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user instanceof User) {
            if ($user->isPelanggan()) {
                if ($request->is('login')) {
                    return redirect()->route('home');
                }

                if (! $this->isCustomerPage($request)) {
                    abort(403, __('Akses ditolak. Pelanggan tidak memiliki akses ke area staf.'));
                }

                return $next($request);
            }

            if (! $user->canAccessStaffWeb()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', __('Sesi Anda telah berakhir. Silakan masuk kembali.'));
            }
        }

        return $next($request);
    }

    private function isCustomerPage(Request $request): bool
    {
        return $request->is('/', 'masuk*', 'daftar*', 'keluar*', 'auth/google*', 'livewire/*');
    }
}
