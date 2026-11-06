<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface font-sans text-ink antialiased selection:bg-primary-soft selection:text-primary flex flex-col justify-between">
        {{-- Navbar Pelanggan Resmi SIGALON --}}
        <x-customer-navbar />

        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer Sesuai Desain Sistem --}}
        <footer class="border-t border-line bg-white py-10 text-sm text-muted">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <x-app-logo />
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-medium">
                        <a href="{{ route('home') }}#hero" class="transition hover:text-primary">{{ __('Beranda') }}</a>
                        <a href="{{ route('home') }}#harga" class="transition hover:text-primary">{{ __('Layanan & Harga') }}</a>
                        <a href="{{ route('home') }}#cara-pesan" class="transition hover:text-primary">{{ __('Cara Pesan') }}</a>
                        <a href="{{ route('home') }}#lokasi-cabang" class="transition hover:text-primary">{{ __('Lokasi Cabang') }}</a>
                        <a href="{{ route('login') }}" class="text-primary hover:underline">{{ __('Portal Masuk Staf') }}</a>
                    </div>
                </div>

                <div class="mt-8 border-t border-line pt-6 text-center text-xs text-muted">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'SIGALON') }} — CV. Aufa. Digitalisasi Distribusi Air Minum Galon. Seluruh hak cipta dilindungi.</p>
                </div>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
