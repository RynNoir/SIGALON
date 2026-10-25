<x-layouts::app :title="__('Kelola Cabang')">
    <div class="flex flex-col gap-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card md:flex-row md:items-end md:justify-between">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Master Data') }}</span>
                <flux:heading size="xl" level="1" class="text-deep!">{{ __('Kelola Cabang') }}</flux:heading>
                <flux:text class="text-muted!">
                    {{ __('Daftar seluruh cabang SIGALON beserta koordinat lokasi dan status operasional.') }}
                </flux:text>
            </div>

            <div>
                <flux:button
                    variant="primary"
                    icon="plus"
                    :href="route('admin.cabang.create')"
                    wire:navigate
                    data-test="tambah-cabang-button"
                >
                    {{ __('Tambah Cabang') }}
                </flux:button>
            </div>
        </div>

        {{-- Flash status notification --}}
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800" role="alert" data-test="flash-status">
                {{ session('status') }}
            </div>
        @endif

        {{-- Tabel Daftar Cabang --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card">
            @if ($cabangs->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center" data-test="cabang-empty">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-surface-low text-muted">
                        <flux:icon.building-storefront />
                    </span>
                    <flux:heading class="mt-4">{{ __('Belum ada cabang terdaftar') }}</flux:heading>
                    <flux:text class="mt-1 text-muted!">{{ __('Silakan tambahkan data cabang pertama Anda.') }}</flux:text>
                    <flux:button
                        variant="primary"
                        icon="plus"
                        :href="route('admin.cabang.create')"
                        class="mt-4"
                        wire:navigate
                    >
                        {{ __('Tambah Cabang') }}
                    </flux:button>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" data-test="tabel-cabang">
                        <thead>
                            <tr class="bg-surface-low text-left text-xs font-semibold uppercase tracking-wider text-muted">
                                <th scope="col" class="rounded-l-lg px-4 py-3">{{ __('Nama Cabang') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Alamat') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Koordinat (Lat, Lng)') }}</th>
                                <th scope="col" class="px-4 py-3">{{ __('Status') }}</th>
                                <th scope="col" class="rounded-r-lg px-4 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @foreach ($cabangs as $item)
                                <tr wire:key="cabang-row-{{ $item->id }}" data-test="cabang-row-{{ $item->id }}">
                                    <th scope="row" class="px-4 py-3.5 text-left font-semibold text-ink">
                                        {{ $item->nama }}
                                    </th>
                                    <td class="max-w-xs truncate px-4 py-3.5 text-ink">
                                        {{ $item->alamat }}
                                    </td>
                                    <td class="px-4 py-3.5 text-muted">
                                        @if ($item->latitude && $item->longitude)
                                            <span>{{ $item->latitude }}, {{ $item->longitude }}</span>
                                        @else
                                            <span class="italic text-muted/60">{{ __('Belum diatur') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($item->is_active)
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                {{ __('Aktif') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-zinc-50 px-2 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-500/10">
                                                {{ __('Nonaktif') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="pencil-square"
                                            :href="route('admin.cabang.edit', $item)"
                                            wire:navigate
                                            data-test="edit-cabang-{{ $item->id }}"
                                        >
                                            {{ __('Ubah') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($cabangs->hasPages())
                    <div class="mt-4 border-t border-line pt-4">
                        {{ $cabangs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-layouts::app>
