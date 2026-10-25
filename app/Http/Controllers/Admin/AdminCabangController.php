<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminCabangController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user && $user->isSuperAdmin(), 403);
    }

    private function ensureAdminCabang(User $user): void
    {
        abort_unless($user->role === Role::AdminCabang, 404);
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $adminCabangs = User::query()
            ->where('role', Role::AdminCabang)
            ->with('cabang')
            ->orderBy('name')
            ->paginate(10);

        return view('pages.admin.admin-cabang.index', [
            'adminCabangs' => $adminCabangs,
        ]);
    }

    public function create(): View
    {
        $this->authorizeSuperAdmin();

        $cabangs = Cabang::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('pages.admin.admin-cabang.create', [
            'cabangs' => $cabangs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'cabang_id' => ['required', 'integer', 'exists:cabang,id'],
        ]);

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = Role::AdminCabang;
        $user->cabang_id = (int) $validated['cabang_id'];
        $user->is_active = true;
        $user->save();

        return redirect()
            ->route('admin.admin-cabang.index')
            ->with('status', __('Akun Admin Cabang berhasil dibuat.'));
    }

    public function edit(User $user): View
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminCabang($user);

        $cabangs = Cabang::query()
            ->where('is_active', true)
            ->orWhere('id', $user->cabang_id)
            ->orderBy('nama')
            ->get();

        return view('pages.admin.admin-cabang.edit', [
            'user' => $user,
            'cabangs' => $cabangs,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminCabang($user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'cabang_id' => ['required', 'integer', 'exists:cabang,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->cabang_id = (int) $validated['cabang_id'];
        $user->save();

        return redirect()
            ->route('admin.admin-cabang.index')
            ->with('status', __('Data Admin Cabang berhasil diperbarui.'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminCabang($user);

        $user->is_active = ! $user->is_active;
        $user->save();

        $pesan = $user->is_active
            ? __('Akun Admin Cabang berhasil diaktifkan.')
            : __('Akun Admin Cabang berhasil dinonaktifkan.');

        return redirect()
            ->route('admin.admin-cabang.index')
            ->with('status', $pesan);
    }

    public function showResetPassword(User $user): View
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminCabang($user);

        return view('pages.admin.admin-cabang.reset-password', [
            'user' => $user,
        ]);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $this->ensureAdminCabang($user);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('admin.admin-cabang.index')
            ->with('status', __('Kata sandi Admin Cabang berhasil direset.'));
    }
}
