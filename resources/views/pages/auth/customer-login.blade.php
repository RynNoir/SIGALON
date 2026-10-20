<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <title>{{ __('Masuk / Daftar Pelanggan') }} - {{ config('app.name', 'SIGALON') }}</title>
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
                    x-data="{ tab: '{{ $errors->has('name') || old('is_register') ? 'register' : 'login' }}', submitting: false }"
                    class="flex w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-card md:flex-row"
                >
                    <!-- Panel Info Depot (Kiri) -->
                    <div class="flex w-full flex-col justify-between border-b border-line bg-surface-low p-6 md:w-5/12 md:border-b-0 md:border-r md:p-8">
                        <div class="flex flex-col gap-6">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-xl bg-primary text-white">
                                    <x-app-logo-icon class="size-6" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-ink">CV. Aufa Depot</h3>
                                    <p class="text-xs text-muted">Sentra Air Minum Higienis</p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Kemudahan Akun SIGALON') }}</p>

                                <div class="flex items-start gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary font-bold text-xs">
                                        COD
                                    </div>
                                    <div class="text-xs text-muted">
                                        <strong class="text-ink font-semibold block text-sm">{{ __('Pesan Antar COD (Rp7.000)') }}</strong>
                                        {{ __('Kurir resmi CV. Aufa mengantar langsung ke pintu Anda, bayar saat galon tiba.') }}
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary">
                                        <flux:icon.check-circle class="size-5" />
                                    </div>
                                    <div class="text-xs text-muted">
                                        <strong class="text-ink font-semibold block text-sm">{{ __('Kualitas Air Terjamin') }}</strong>
                                        {{ __('Diproses dengan standar operasional ketat untuk higienitas dan kesegaran air.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 rounded-xl bg-white p-4 border border-line text-xs text-muted">
                            <p class="flex items-center gap-2">
                                <flux:icon.shield-check class="size-4 text-primary shrink-0" />
                                <span>{{ __('Data nomor WhatsApp Anda aman & hanya digunakan untuk koordinasi pengantaran pesanan.') }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Panel Formulir (Kanan) -->
                    <div class="flex w-full flex-col justify-center p-6 md:w-7/12 md:p-10">
                        <div class="flex flex-col gap-1 mb-6">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Portal Pelanggan Resmi') }}</span>
                            <flux:heading size="xl" level="1" class="text-ink!">{{ __('Selamat Datang di SIGALON') }}</flux:heading>
                            <flux:text class="text-muted!">{{ __('Akses pemesanan air minum isi ulang CV. Aufa Tirta Abadi.') }}</flux:text>
                        </div>

                        <!-- Tab Navigasi Masuk / Daftar -->
                        <div class="grid grid-cols-2 gap-2 rounded-xl bg-surface-low p-1.5 mb-6">
                            <button
                                type="button"
                                x-on:click="tab = 'login'"
                                class="rounded-lg py-2 text-sm font-semibold transition"
                                x-bind:class="tab === 'login' ? 'bg-primary text-white shadow-xs' : 'text-muted hover:text-ink'"
                            >
                                {{ __('Masuk Akun') }}
                            </button>
                            <button
                                type="button"
                                x-on:click="tab = 'register'"
                                class="rounded-lg py-2 text-sm font-semibold transition"
                                x-bind:class="tab === 'register' ? 'bg-primary text-white shadow-xs' : 'text-muted hover:text-ink'"
                            >
                                {{ __('Daftar Baru') }}
                            </button>
                        </div>

                        <!-- Tombol Masuk dengan Google -->
                        <a
                            href="{{ route('auth.google.redirect') }}"
                            class="flex w-full items-center justify-center gap-3 rounded-lg border border-line bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-xs transition hover:bg-surface-low"
                            data-test="google-login-button"
                        >
                            <svg class="size-5" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                            </svg>
                            <span>{{ __('Masuk dengan Google') }}</span>
                        </a>

                        <div class="relative my-4 flex items-center justify-center">
                            <div class="w-full border-t border-line"></div>
                            <span class="absolute bg-white px-3 text-xs uppercase tracking-wider text-muted">{{ __('Atau nomor WhatsApp / HP') }}</span>
                        </div>

                        <!-- Form Masuk -->
                        <div x-show="tab === 'login'" x-cloak>
                            <form
                                method="POST"
                                action="{{ route('customer.login.store') }}"
                                class="flex flex-col gap-4"
                                x-on:submit="submitting = true"
                            >
                                @csrf

                                <div>
                                    <flux:input
                                        name="phone"
                                        :label="__('Nomor WhatsApp / HP Aktif')"
                                        :value="old('phone')"
                                        type="tel"
                                        required
                                        autofocus
                                        placeholder="812-3456-7890"
                                    >
                                        <x-slot name="leading">
                                            <span class="text-sm font-semibold text-muted">+62</span>
                                        </x-slot>
                                    </flux:input>
                                    <p class="mt-1 text-xs text-muted">{{ __('Masukkan nomor WhatsApp yang Anda gunakan untuk pemesanan galon.') }}</p>
                                </div>

                                <flux:input
                                    name="password"
                                    :label="__('Kata Sandi')"
                                    type="password"
                                    required
                                    placeholder="Masukkan kata sandi akun"
                                    viewable
                                />

                                <div class="flex items-center justify-between text-sm">
                                    <flux:checkbox name="remember" :label="__('Ingat saya di perangkat ini')" :checked="old('remember')" />
                                    <a
                                        href="{{ $whatsappUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-xs font-semibold text-primary hover:underline"
                                    >
                                        {{ __('Lupa Kata Sandi? Bantuan Admin') }}
                                    </a>
                                </div>

                                <flux:button
                                    variant="primary"
                                    type="submit"
                                    class="w-full"
                                    data-test="customer-login-submit"
                                    x-bind:disabled="submitting"
                                >
                                    <span x-show="! submitting">{{ __('Masuk ke Akun SIGALON') }} &rarr;</span>
                                    <span x-show="submitting" x-cloak>{{ __('Memproses…') }}</span>
                                </flux:button>
                            </form>
                        </div>

                        <!-- Form Daftar Baru -->
                        <div x-show="tab === 'register'" x-cloak>
                            <form
                                method="POST"
                                action="{{ route('customer.register') }}"
                                class="flex flex-col gap-4"
                                x-on:submit="submitting = true"
                            >
                                @csrf
                                <input type="hidden" name="is_register" value="1">

                                <flux:input
                                    name="name"
                                    :label="__('Nama Lengkap')"
                                    :value="old('name')"
                                    type="text"
                                    required
                                    placeholder="Nama Anda"
                                />

                                <div>
                                    <flux:input
                                        name="phone"
                                        :label="__('Nomor WhatsApp / HP Aktif')"
                                        :value="old('phone')"
                                        type="tel"
                                        required
                                        placeholder="812-3456-7890"
                                    >
                                        <x-slot name="leading">
                                            <span class="text-sm font-semibold text-muted">+62</span>
                                        </x-slot>
                                    </flux:input>
                                    <p class="mt-1 text-xs text-muted">{{ __('Digunakan kurir untuk konfirmasi dan pengantaran galon.') }}</p>
                                </div>

                                <flux:input
                                    name="password"
                                    :label="__('Kata Sandi')"
                                    type="password"
                                    required
                                    placeholder="Minimal 8 karakter"
                                    viewable
                                />

                                <flux:input
                                    name="password_confirmation"
                                    :label="__('Konfirmasi Kata Sandi')"
                                    type="password"
                                    required
                                    placeholder="Ulangi kata sandi"
                                    viewable
                                />

                                <flux:button
                                    variant="primary"
                                    type="submit"
                                    class="w-full"
                                    data-test="customer-register-submit"
                                    x-bind:disabled="submitting"
                                >
                                    <span x-show="! submitting">{{ __('Daftar Akun Baru') }} &rarr;</span>
                                    <span x-show="submitting" x-cloak>{{ __('Mendaftarkan…') }}</span>
                                </flux:button>
                            </form>
                        </div>

                        <!-- Footer Badges -->
                        <div class="mt-6 flex flex-wrap items-center justify-between border-t border-line pt-4 text-xs text-muted">
                            <span class="flex items-center gap-1">
                                <flux:icon.check class="size-3.5 text-success" />
                                {{ __('Bebas Biaya Registrasi') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <flux:icon.lock-closed class="size-3.5 text-muted" />
                                {{ __('Privasi Terlindungi') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <flux:icon.user-group class="size-3.5 text-primary" />
                                {{ __('Layanan Pelanggan Aktif') }}
                            </span>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="w-full border-t border-line bg-white py-4 text-center text-xs text-muted">
                &copy; {{ date('Y') }} {{ config('app.name', 'SIGALON') }} &middot; CV. Aufa Tirta Abadi
            </footer>
        </div>

        @fluxScripts
    </body>
</html>
