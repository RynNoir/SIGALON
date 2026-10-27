<?php

namespace App\Http\Controllers\Cabang;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StafController extends Controller
{
    private function authorizeAdminCabang(): User
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user && $user->isAdminCabang(), 403);
        abort_if($user->cabang_id === null, 403);

        return $user;
    }

    private function ensureStafSameCabang(User $staf, User $admin): void
    {
        abort_unless(in_array($staf->role, [Role::Kasir, Role::Kurir], true), 404);
        abort_unless($staf->cabang_id === $admin->cabang_id, 403);
    }

    public function index(): View
    {
        $admin = $this->authorizeAdminCabang();

        $stafs = User::query()
            ->where('cabang_id', $admin->cabang_id)
            ->whereIn('role', [Role::Kasir, Role::Kurir])
            ->orderBy('name')
            ->paginate(10);

        return view('pages.cabang.staf.index', [
            'admin' => $admin,
            'stafs' => $stafs,
        ]);
    }

    public function create(): View
    {
        $admin = $this->authorizeAdminCabang();

        return view('pages.cabang.staf.create', [
            'admin' => $admin,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $admin = $this->authorizeAdminCabang();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in([Role::Kasir->value, Role::Kurir->value])],
        ]);

        $staf = new User;
        $staf->name = $validated['name'];
        $staf->email = $validated['email'];
        $staf->password = Hash::make($validated['password']);
        $staf->role = Role::from($validated['role']);
        $staf->cabang_id = $admin->cabang_id;
        $staf->is_active = true;
        $staf->save();

        return redirect()
            ->route('cabang.staf.index')
            ->with('status', __('Akun staf berhasil dibuat.'));
    }

    public function edit(User $user): View
    {
        $admin = $this->authorizeAdminCabang();
        $this->ensureStafSameCabang($user, $admin);

        return view('pages.cabang.staf.edit', [
            'admin' => $admin,
            'staf' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $admin = $this->authorizeAdminCabang();
        $this->ensureStafSameCabang($user, $admin);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in([Role::Kasir->value, Role::Kurir->value])],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = Role::from($validated['role']);
        $user->save();

        return redirect()
            ->route('cabang.staf.index')
            ->with('status', __('Data staf berhasil diperbarui.'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $admin = $this->authorizeAdminCabang();
        $this->ensureStafSameCabang($user, $admin);

        $user->is_active = ! $user->is_active;
        $user->save();

        $pesan = $user->is_active
            ? __('Akun staf berhasil diaktifkan.')
            : __('Akun staf berhasil dinonaktifkan.');

        return redirect()
            ->route('cabang.staf.index')
            ->with('status', $pesan);
    }

    public function showResetPassword(User $user): View
    {
        $admin = $this->authorizeAdminCabang();
        $this->ensureStafSameCabang($user, $admin);

        return view('pages.cabang.staf.reset-password', [
            'admin' => $admin,
            'staf' => $user,
        ]);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $admin = $this->authorizeAdminCabang();
        $this->ensureStafSameCabang($user, $admin);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('cabang.staf.index')
            ->with('status', __('Kata sandi staf berhasil direset.'));
    }
}
