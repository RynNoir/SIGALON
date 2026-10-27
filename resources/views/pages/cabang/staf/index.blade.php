<x-layouts::app :title="__('Kelola Staf Cabang')">
    <div class="flex flex-col gap-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card md:flex-row md:items-end md:justify-between">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Manajemen Staf') }}</span>
                <flux:heading size="xl" level="1" class="text-deep!">{{ __('Kelola Staf Cabang') }}</flux:heading>
                <flux:text class="text-muted!">
                    {{ __('Daftar staf Kasir dan Kurir di :cabang beserta status keaktifan akun.', ['cabang' => $admin->cabang?->nama ?? '-']) }}
                </flux:text>
            </div>

            <div>
                <flux:button
                    variant="primary"
                    icon="plus"
                    :href="route('cabang.staf.create')"
                    wire:navigate
                    data-test="tambah-staf-button"
                >
                    {{ __('Tambah Staf') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash status notification --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800" role="alert" data-test="flash-status">
                {{ session('status') }}
            </div>
        @endif

        {{-- Tabel Daftar Staf --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card">
            @if ($stafs->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center" data-test="staf-empty">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-surface-low text-muted">
                        <flux:icon.users />
                    </span>
                    <flux:heading class="mt-4">{{ __('Belum ada staf Kasir atau Kurir') }}</flux:heading>
                    <flux:text class="mt-1 text-muted!">{{ __('Silakan tambah akun staf baru untuk operasional cabang Anda.') }}</flux:text>
                    <flux:button
                        variant="primary"
                        icon="plus"
                        :href="route('cabang.staf.create')"
                        class="mt-4"
                        wire:navigate
                    >
                        {{ __('Tambah Staf') }}
                    </flux:button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-test="tabel-staf">
                        <thead>
                            <tr class="bg-surface-low text-left text-xs font-semibold uppercase tracking-wider text-muted">
                                <th scope="col" class="rounded-l-lg px-4 py-3">{{ __('Nama Staf') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Email') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Peran') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Status Akun') }}</th>
                                <th scope="col" class="rounded-r-lg px-4 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($stafs as $item)
                                <tr wire:key="staf-row-{{ $item->id }}" data-test="staf-row-{{ $item->id }}">
                                    <th scope="row" class="px-4 py-3.5 text-left font-semibold text-ink">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-primary">
                                                {{ $item->initials() }}
                                            </span>
                                            <span>{{ $item->name }}</span>
                                        </div>
                                    </th>
                                    <td class="px-4 py-3.5 text-muted">
                                        {{ $item->email }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($item->role === \App\Enums\Role::Kasir)
                                            <span class="inline-flex items-center rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-semibold text-cyan-800" data-test="badge-role-kasir">
                                                {{ __('Kasir') }}
                                            </span>
                                        @elseif ($item->role === \App\Enums\Role::Kurir)
                                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800" data-test="badge-role-kurir">
                                                {{ __('Kurir') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($item->is_active)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700" data-test="status-aktif">
                                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                {{ __('Aktif') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600" data-test="status-nonaktif">
                                                <span class="size-1.5 rounded-full bg-zinc-400"></span>
                                                {{ __('Nonaktif') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                :href="route('cabang.staf.edit', $item)"
                                                wire:navigate
                                                data-test="edit-staf-{{ $item->id }}"
                                            >
                                                {{ __('Ubah') }}
                                            </flux:button>

                                            <flux:button
                                                size="sm"
                                                variant="ghost"
                                                :href="route('cabang.staf.reset-password', $item)"
                                                wire:navigate
                                                data-test="reset-password-staf-{{ $item->id }}"
                                            >
                                                {{ __('Reset Sandi') }}
                                            </flux:button>

                                            <form
                                                method="POST"
                                                action="{{ route('cabang.staf.toggle-active', $item) }}"
                                                class="inline"
                                                onsubmit="return confirm('{{ $item->is_active ? __('Nonaktifkan akun staf ini?') : __('Aktifkan kembali akun staf ini?') }}')"
                                            >
                                                @csrf
                                                <flux:button
                                                    size="sm"
                                                    variant="ghost"
                                                    type="submit"
                                                    class="{{ $item->is_active ? 'text-amber-700! hover:bg-amber-50!' : 'text-emerald-700! hover:bg-emerald-50!' }}"
                                                    data-test="toggle-staf-{{ $item->id }}"
                                                >
                                                    {{ $item->is_active ? __('Nonaktifkan') : __('Aktifkan') }}
                                                </flux:button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($stafs->hasPages())
                    <div class="pt-4 border-t border-line">
                        {{ $stafs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts::app>
