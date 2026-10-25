<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjaga batas website staf:
 * - sesi staf yang tidak lagi berhak (nonaktif, Admin Cabang/Kasir tanpa cabang) dikeluarkan ke halaman masuk;
 * - sesi Kurir dikeluarkan ke halaman akses ditolak (Kurir hanya memakai aplikasi Android);
 * - pelanggan (guard `pelanggan`) yang membuka area staf mendapat 403.
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
        $staff = Auth::guard('web')->user();

        if ($staff instanceof User && ! $staff->canAccessStaffWeb()) {
            $wrongRole = ! $staff->role->canAccessWeb();

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $wrongRole
                ? redirect()->route('akses-ditolak')
                : redirect()->route('login')->with('status', __('Sesi Anda telah berakhir. Silakan masuk kembali.'));
        }

        if (! $staff && Auth::guard('pelanggan')->check()) {
            if ($request->routeIs('login')) {
                return redirect()->route('home');
            }

            if ($this->isStaffArea($request)) {
                abort(403);
            }
        }

        return $next($request);
    }

    /**
     * Rute staf adalah rute yang dilindungi middleware `auth` (guard web).
     */
    private function isStaffArea(Request $request): bool
    {
        $middleware = $request->route()?->gatherMiddleware() ?? [];

        foreach ($middleware as $name) {
            if (is_string($name) && ($name === 'auth' || $name === 'auth:web')) {
                return true;
            }
        }

        return false;
    }
}
