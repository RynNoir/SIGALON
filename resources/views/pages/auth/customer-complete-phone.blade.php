<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <title>{{ __('Lengkapi Nomor WhatsApp') }} - {{ config('app.name', 'SIGALON') }}</title>
    </head>
    <body class="min-h-screen bg-surface font-sans text-ink antialiased">
        <div class="flex min-h-screen flex-col justify-between">
            <header class="w-full border-b border-line bg-white px-6 py-4">
                <div class="mx-auto flex max-w-5xl items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <x-app-logo />
                    </a>
                    <a
                        href="{{ route('home') }}"
                        class="text-sm font-medium text-muted hover:text-primary transition"
                    >
                        &larr; {{ __('Kembali ke Beranda') }}
                    </a>
                </div>
            </header>

            <main class="flex flex-1 items-center justify-center p-4 sm:p-6 md:p-10">
                <div
                    x-data="{ submitting: false }"
                    class="flex w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-card p-6 sm:p-8 md:p-10"
                >
                    <div class="flex flex-col gap-1 mb-6 text-center">
                        <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Lengkapi Data Pelanggan') }}</span>
                        <flux:heading size="xl" level="1" class="text-ink!">{{ __('Nomor WhatsApp untuk Pengantaran') }}</flux:heading>
                        <flux:text class="text-muted!">
                            {{ __('Halo, :name. Lengkapi nomor WhatsApp aktif Anda agar kurir resmi depot dapat berkoordinasi dan mengantar galon ke alamat Anda.', ['name' => $pelanggan->nama]) }}
                        </flux:text>
                    </div>

                    {{-- Info Box --}}
                    <div class="mb-6 rounded-xl bg-surface-low p-4 border border-line text-xs text-muted flex items-start gap-3">
                        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary">
                            <flux:icon.shield-check class="size-4" />
                        </div>
                        <div>
                            <strong class="text-ink font-semibold block">{{ __('Privasi Nomor Terlindungi') }}</strong>
                            <span>{{ __('Nomor WhatsApp hanya digunakan untuk keperluan pengantaran pesanan air galon CV. Aufa Depot.') }}</span>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('customer.phone.update') }}"
                        class="flex flex-col gap-5"
                        x-on:submit="submitting = true"
                    >
                        @csrf

                        <div>
                            <flux:input
                                name="phone"
                                :label="__('Nomor WhatsApp / HP Aktif')"
                                :value="old('phone', $pelanggan->no_hp ? '0'.substr($pelanggan->no_hp, 2) : '')"
                                type="tel"
                                required
                                autofocus
                                placeholder="812-3456-7890"
                            >
                                <x-slot name="leading">
                                    <span class="text-sm font-semibold text-muted">+62</span>
                                </x-slot>
                            </flux:input>
                            <p class="mt-1 text-xs text-muted">{{ __('Masukkan nomor aktif (minimal 9 digit), diawali dengan 08 atau 8.') }}</p>
                        </div>

                        <flux:button
                            variant="primary"
                            type="submit"
                            class="w-full"
                            data-test="customer-complete-phone-submit"
                            x-bind:disabled="submitting"
                        >
                            <span x-show="! submitting">{{ __('Simpan Nomor WhatsApp') }} &rarr;</span>
                            <span x-show="submitting" x-cloak>{{ __('Menyimpan…') }}</span>
                        </flux:button>
                    </form>

                    <div class="mt-6 flex items-center justify-center border-t border-line pt-4 text-center">
                        <a href="{{ route('home') }}" class="text-xs font-medium text-muted hover:text-primary transition">
                            {{ __('Lewati untuk sekarang dan kembali ke beranda') }}
                        </a>
                    </div>
                </div>
            </main>

            <footer class="w-full border-t border-line bg-white py-4 text-center text-xs text-muted">
                &copy; {{ date('Y') }} {{ config('app.name', 'SIGALON') }} &middot; CV. Aufa
            </footer>
        </div>

        @fluxScripts
    </body>
</html>

