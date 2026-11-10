@php
    $currentUser = auth()->user();
    $lokasiAktif = $currentUser->isSuperAdmin() ? __('Semua cabang') : $currentUser->cabang?->nama;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-surface text-ink">
    <flux:sidebar sticky collapsible="mobile" class="border-e border-line bg-white">
        <flux:sidebar.header>
            <div class="flex items-center gap-3">
                <div
                    class="flex aspect-square size-9 items-center justify-center rounded-xl bg-primary text-white shadow-xs">
                    <x-app-logo-icon class="size-5" />
                </div>
                <div class="grid leading-tight">
                    <span class="text-base font-bold text-deep tracking-tight">SIGALON</span>
                    <span class="text-[10px] font-medium text-muted">CV. AUFA</span>
                </div>
            </div>
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <div class="flex items-center justify-between gap-2 rounded-xl bg-surface-low px-3 py-2.5"
            data-test="lokasi-aktif">
            <div class="flex items-center gap-2 min-w-0">
                <flux:icon.building-storefront variant="mini" class="shrink-0 text-primary" />
                <div class="grid min-w-0 leading-tight">
                    <span
                        class="text-[10px] font-semibold uppercase tracking-wider text-muted">{{ __('Lokasi aktif') }}</span>
                    <span class="truncate text-xs font-bold text-ink">{{ $lokasiAktif }}</span>
                </div>
            </div>
            @if ($currentUser->isSuperAdmin())
                <flux:icon.chevron-down variant="mini" class="shrink-0 text-muted size-3.5" />
            @endif
        </div>

        <flux:sidebar.nav>
            <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard*')"
                class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                wire:navigate>
                {{ __('Dasbor') }}
            </flux:sidebar.item>

            @if ($currentUser->isSuperAdmin())
                <flux:sidebar.item icon="building-office-2" :href="route('admin.cabang.index')"
                    :current="request()->routeIs('admin.cabang.*')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Kelola Cabang') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="users" :href="route('admin.admin-cabang.index')"
                    :current="request()->routeIs('admin.admin-cabang.*')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Admin Cabang') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="qr-code" :href="route('admin.qr-statis')"
                    :current="request()->routeIs('admin.qr-statis')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Gambar QR') }}
                </flux:sidebar.item>
            @endif

            @if ($currentUser->isAdminCabang())
                <flux:sidebar.item icon="users" :href="route('cabang.staf.index')"
                    :current="request()->routeIs('cabang.staf.*')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Kelola Staf') }}
                </flux:sidebar.item>
            @endif

            @if ($currentUser->role === \App\Enums\Role::Kasir)
                <flux:sidebar.item icon="banknotes" :href="route('kasir.transaksi')"
                    :current="request()->routeIs('kasir.transaksi*')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Transaksi Walk-in') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="truck" :href="route('kasir.pesanan-cod')"
                    :current="request()->routeIs('kasir.pesanan-cod*')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Pesanan COD') }}
                </flux:sidebar.item>
            @endif

            @if ($currentUser->isAdminCabang() || $currentUser->role === \App\Enums\Role::Kasir)
                <flux:sidebar.item icon="clipboard-document-list" :href="route('cabang.pesanan')"
                    :current="request()->routeIs('cabang.pesanan')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Daftar Pesanan') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="queue-list" :href="route('cabang.transaksi')"
                    :current="request()->routeIs('cabang.transaksi*')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate>
                    {{ __('Transaksi') }}
                </flux:sidebar.item>
            @endif
        </flux:sidebar.nav>

        <flux:spacer />

        <x-desktop-user-menu class="hidden lg:block" :name="$currentUser->name" />
    </flux:sidebar>

    <!-- Desktop Header / Topbar -->
    <flux:header class="hidden lg:flex border-b border-line bg-white px-8 py-3.5 items-center justify-end">
        <div class="flex items-center gap-4">
            <div
                class="flex items-center gap-2 rounded-xl border border-line bg-surface-low/50 px-3 py-1.5 text-xs font-medium text-ink">
                <flux:icon.calendar variant="mini" class="text-primary size-4" />
                <span>{{ __('Hari ini: :tanggal', ['tanggal' => now()->translatedFormat('d F Y')]) }}</span>
            </div>

            <button type="button" aria-label="{{ __('Notifikasi') }}"
                class="relative rounded-xl border border-line p-2 text-muted hover:bg-surface hover:text-ink transition shadow-xs">
                <flux:icon.bell variant="mini" class="size-4" />
            </button>

            <div class="flex items-center gap-3 border-l border-line pl-4">
                <flux:avatar :name="$currentUser->name" :initials="$currentUser->initials()" size="sm"
                    class="ring-2 ring-primary-soft" />
                <div class="text-left text-xs leading-tight">
                    <div class="font-bold text-ink">{{ $currentUser->name }}</div>
                    <div class="text-[11px] text-muted">{{ $currentUser->role->label() }}</div>
                </div>
            </div>
        </div>
    </flux:header>

    <!-- Mobile User Menu -->
    <flux:header class="border-b border-line bg-white lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="$currentUser->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="$currentUser->name" :initials="$currentUser->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ $currentUser->name }}</flux:heading>
                                <flux:text class="truncate">{{ $currentUser->role->label() }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Pengaturan') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Keluar') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>