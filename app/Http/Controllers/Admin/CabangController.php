<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CabangController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user && $user->isSuperAdmin(), 403);
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $cabangs = Cabang::query()
            ->orderBy('nama')
            ->paginate(10);

        return view('pages.admin.cabang.index', [
            'cabangs' => $cabangs,
        ]);
    }

    public function create(): View
    {
        $this->authorizeSuperAdmin();

        return view('pages.admin.cabang.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        Cabang::create([
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.cabang.index')
            ->with('status', __('Data cabang berhasil ditambahkan.'));
    }

    public function edit(Cabang $cabang): View
    {
        $this->authorizeSuperAdmin();

        return view('pages.admin.cabang.edit', [
            'cabang' => $cabang,
        ]);
    }

    public function update(Request $request, Cabang $cabang): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $cabang->update([
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return redirect()
            ->route('admin.cabang.index')
            ->with('status', __('Data cabang berhasil diperbarui.'));
    }
}
