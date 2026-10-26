<x-layouts::app :title="__('Kelola Admin Cabang')">
    <div class="flex flex-col gap-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card md:flex-row md:items-end md:justify-between">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Manajemen Akun') }}</span>
                <flux:heading size="xl" level="1" class="text-deep!">{{ __('Kelola Admin Cabang') }}</flux:heading>
                <flux:text class="text-muted!">
                    {{ __('Daftar staf Admin Cabang beserta cabang penempatan dan status keaktifan akun.') }}
                </flux:text>
            </div>

            <div>
                <flux:button
                    variant="primary"
                    icon="plus"
                    :href="route('admin.admin-cabang.create')"
                    wire:navigate
                    data-test="tambah-admin-cabang-button"
                >
                    {{ __('Tambah Admin Cabang') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash status notification --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800" role="alert" data-test="flash-status">
                {{ session('status') }}
            </div>
        @endif

        {{-- Tabel Daftar Admin Cabang --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card">
            @if ($adminCabangs->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center" data-test="admin-cabang-empty">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-surface-low text-muted">
                        <flux:icon.users />
                    </span>
                    <flux:heading class="mt-4">{{ __('Belum ada akun Admin Cabang') }}</flux:heading>
                    <flux:text class="mt-1 text-muted!">{{ __('Silakan buat akun Admin Cabang untuk mengelola cabang.') }}</flux:text>
                    <flux:button
                        variant="primary"
                        icon="plus"
                        :href="route('admin.admin-cabang.create')"
                        class="mt-4"
                        wire:navigate
                    >
                        {{ __('Tambah Admin Cabang') }}
                    </flux:button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-test="tabel-admin-cabang">
                        <thead>
                            <tr class="bg-surface-low text-left text-xs font-semibold uppercase tracking-wider text-muted">
                                <th scope="col" class="rounded-l-lg px-4 py-3">{{ __('Nama Staf') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Email') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Cabang') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Status Akun') }}</th>
                                <th scope="col" class="rounded-r-lg px-4 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($adminCabangs as $item)
                                <tr wire:key="admin-cabang-row-{{ $item->id }}" data-test="admin-cabang-row-{{ $item->id }}">
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
                                    <td class="px-4 py-3.5 text-ink">
                                        {{ $item->cabang?->nama ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($item->is_active)
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20" data-test="badge-aktif-{{ $item->id }}">
                                                {{ __('Aktif') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-zinc-50 px-2 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10" data-test="badge-nonaktif-{{ $item->id }}">
                                                {{ __('Nonaktif') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="pencil-square"
                                                :href="route('admin.admin-cabang.edit', $item)"
                                                wire:navigate
                                                data-test="edit-admin-{{ $item->id }}"
                                            >
                                                {{ __('Ubah') }}
                                            </flux:button>

                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="key"
                                                :href="route('admin.admin-cabang.reset-password', $item)"
                                                wire:navigate
                                                data-test="reset-password-admin-{{ $item->id }}"
                                            >
                                                {{ __('Reset Password') }}
                                            </flux:button>

                                            <form
                                                method="POST"
                                                action="{{ route('admin.admin-cabang.toggle-active', $item) }}"
                                                class="inline"
                                                onsubmit="return confirm('{{ $item->is_active ? __('Apakah Anda yakin ingin menonaktifkan akun ini? Sesi staf akan langsung dikeluarkan.') : __('Apakah Anda yakin ingin mengaktifkan kembali akun ini?') }}');"
                                            >
                                                @csrf
                                                @if ($item->is_active)
                                                    <flux:button
                                                        variant="ghost"
                                                        size="sm"
                                                        type="submit"
                                                        class="text-amber-600 hover:text-amber-700"
                                                        data-test="toggle-active-admin-{{ $item->id }}"
                                                    >
                                                        {{ __('Nonaktifkan') }}
                                                    </flux:button>
                                                @else
                                                    <flux:button
                                                        variant="ghost"
                                                        size="sm"
                                                        type="submit"
                                                        class="text-emerald-600 hover:text-emerald-700"
                                                        data-test="toggle-active-admin-{{ $item->id }}"
                                                    >
                                                        {{ __('Aktifkan') }}
                                                    </flux:button>
                                                @endif
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($adminCabangs->hasPages())
                    <div class="mt-4 border-t border-line pt-4">
                        {{ $adminCabangs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts::app>
