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
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <div class="flex items-center gap-2 rounded-xl bg-surface-low px-3 py-2" data-test="lokasi-aktif">
                <flux:icon.building-storefront variant="mini" class="shrink-0 text-primary" />
                <div class="grid min-w-0 leading-tight">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">{{ __('Lokasi aktif') }}</span>
                    <span class="truncate text-sm font-semibold text-ink">{{ $lokasiAktif }}</span>
                </div>
            </div>

            <flux:sidebar.nav>
                <flux:sidebar.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    class="data-current:border-transparent! data-current:bg-primary-strong! data-current:text-white!"
                    wire:navigate
                >
                    {{ __('Dasbor') }}
                </flux:sidebar.item>

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
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
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
