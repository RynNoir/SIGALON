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

        if ($user instanceof User && ! $user->canAccessStaffWeb()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', __('Sesi Anda telah berakhir. Silakan masuk kembali.'));
        }

        return $next($request);
    }
}
