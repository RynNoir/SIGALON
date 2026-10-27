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
            @endif
        </flux:sidebar.nav>

        <flux:spacer />

        <div
            class="rounded-xl border border-line bg-surface-low/70 px-3 py-2 text-xs font-medium text-ink flex items-center justify-between">
            <span class="flex items-center gap-1.5 text-ink">
                <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                Terminal Kasir #01
            </span>
            <span class="font-semibold text-primary">Online</span>
        </div>

        <x-desktop-user-menu class="hidden lg:block" :name="$currentUser->name" />
    </flux:sidebar>

    <!-- Desktop Header / Topbar -->
    <flux:header class="hidden lg:flex border-b border-line bg-white px-8 py-3.5 items-center justify-between">
        <div class="relative w-96">
            <flux:icon.magnifying-glass variant="mini"
                class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted size-4" />
            <input type="search" placeholder="{{ __('Cari pesanan, nota, atau pelanggan...') }}"
                class="w-full rounded-xl border border-line bg-surface py-2 pl-10 pr-4 text-xs text-ink placeholder:text-muted focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-xs" />
        </div>

        <div class="flex items-center gap-4">
            <div
                class="flex items-center gap-2 rounded-xl border border-line bg-surface-low/50 px-3 py-1.5 text-xs font-medium text-ink">
                <flux:icon.calendar variant="mini" class="text-primary size-4" />
                <span>{{ __('Hari ini: :tanggal', ['tanggal' => now()->translatedFormat('d F Y')]) }}</span>
            </div>

            <div
                class="flex items-center gap-2 rounded-full bg-primary-soft/80 border border-primary/20 px-3.5 py-1.5 text-xs font-semibold text-primary">
                <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                <span>{{ __('Status Depot: Buka / Operasional Normal') }}</span>
            </div>

            <button type="button"
                class="relative rounded-xl border border-line p-2 text-muted hover:bg-surface hover:text-ink transition shadow-xs">
                <flux:icon.bell variant="mini" class="size-4" />
                <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-red-500 ring-2 ring-white"></span>
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

                @if ($currentUser->isSuperAdmin())
                    <flux:sidebar.item
                        icon="building-office-2"
                        :href="route('admin.cabang.index')"
                        :current="request()->routeIs('admin.cabang.*')"
                        class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                        wire:navigate
                    >
                        {{ __('Kelola Cabang') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="users"
                        :href="route('admin.admin-cabang.index')"
                        :current="request()->routeIs('admin.admin-cabang.*')"
                        class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                        wire:navigate
                    >
                        {{ __('Admin Cabang') }}
                    </flux:sidebar.item>
                @endif

                @if ($currentUser->isAdminCabang())
                    <flux:sidebar.item
                        icon="users"
                        :href="route('cabang.staf.index')"
                        :current="request()->routeIs('cabang.staf.*')"
                        class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                        wire:navigate
                    >
                        {{ __('Kelola Staf') }}
                    </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="$currentUser->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-line bg-white lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="$currentUser->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="$currentUser->name"
                                    :initials="$currentUser->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ $currentUser->name }}</flux:heading>
                                    <flux:text class="truncate">{{ $currentUser->role->label() }}</flux:text>
                                </div>
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