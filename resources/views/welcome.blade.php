<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth scroll-pt-20">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIGALON') }} — Air Minum Isi Ulang & Pengantaran CV. Aufa</title>
        <meta name="description" content="Layanan resmi distribusi dan pengantaran air minum isi ulang CV. Aufa di Kota Padang. Transparansi harga walk-in dan COD serta lokasi depot cabang.">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        <!-- Leaflet CSS -->
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        />

        <style>
            /* Isolasi layer Leaflet agar tidak pernah menutupi sticky navbar saat di-scroll */
            #branch-map,
            .leaflet-container {
                isolation: isolate;
                z-index: 1 !important;
            }
            .leaflet-pane {
                z-index: 2 !important;
            }
            .leaflet-top,
            .leaflet-bottom {
                z-index: 3 !important;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-surface font-sans text-ink antialiased selection:bg-primary-soft selection:text-primary">
        {{-- Navbar Terpadu --}}
        <x-customer-navbar :whatsapp-url="$whatsappUrl" />

        <main>
            {{-- HERO SECTION --}}
            <section id="hero" class="relative overflow-hidden py-16 sm:py-24 lg:py-28 scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid items-center gap-12 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="inline-flex items-center gap-2 rounded-full bg-primary-soft px-3.5 py-1 text-xs font-semibold tracking-wider text-primary uppercase">
                                <span class="size-2 rounded-full bg-primary"></span>
                                {{ __('Depot Air Minum CV. Aufa') }}
                            </div>

                            <h1 class="mt-4 text-3xl font-bold tracking-tight text-ink sm:text-4xl lg:text-5xl">
                                Air Galon Bersih & Segar, Siap Diantar ke Rumah Anda
                            </h1>

                            <p class="mt-5 text-base leading-relaxed text-muted sm:text-lg">
                                Layanan resmi isi ulang air galon CV. Aufa di Kota Padang. Nikmati kemudahan pesan antar (COD) langsung oleh kurir depot atau ambil langsung di lokasi cabang terdekat dengan harga resmi yang transparan.
                            </p>

                            <div class="mt-8 flex flex-wrap items-center gap-4">
                                <a
                                    href="{{ $whatsappUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2.5 rounded-xl bg-primary px-6 py-3.5 text-base font-semibold text-white shadow-card transition hover:bg-primary-strong focus:outline-hidden focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                    data-test="hero-cta-whatsapp"
                                >
                                    <svg class="size-5 fill-current" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.101.005.24.038.376.365.144.346.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.679.116-.174.231-.145.39-.087s1.011.477 1.184.564c.174.087.289.13.332.203.044.072.044.419-.1 1.024z"/>
                                    </svg>
                                    <span>{{ __('Pesan Air Galon') }}</span>
                                </a>

                                <a
                                    href="#lokasi-cabang"
                                    class="inline-flex items-center gap-2 rounded-xl border border-line bg-white px-5 py-3.5 text-base font-semibold text-ink shadow-2xs transition hover:bg-surface-low focus:outline-hidden"
                                >
                                    <svg class="size-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ __('Lihat Lokasi Cabang') }}</span>
                                </a>
                            </div>

                            <div class="mt-10 grid grid-cols-2 gap-4 border-t border-line pt-6 sm:grid-cols-3">
                                <div>
                                    <div class="text-xs font-semibold uppercase text-muted tracking-wider">{{ __('Harga Walk-in') }}</div>
                                    <div class="mt-1 text-xl font-bold text-primary">Rp{{ number_format($hargaWalkIn, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold uppercase text-muted tracking-wider">{{ __('Harga COD Antar') }}</div>
                                    <div class="mt-1 text-xl font-bold text-primary">Rp{{ number_format($hargaCod, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <div class="text-xs font-semibold uppercase text-muted tracking-wider">{{ __('Jangkauan') }}</div>
                                    <div class="mt-1 text-xl font-bold text-ink">Kota Padang</div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="relative mx-auto max-w-lg lg:max-w-none">
                                {{-- Ambient Decorative Backdrop --}}
                                <div class="absolute -inset-2 rounded-3xl bg-gradient-to-tr from-primary/20 via-primary-soft to-transparent opacity-75 blur-xl"></div>

                                {{-- Main Photo Frame --}}
                                <div class="relative overflow-hidden rounded-3xl border border-line bg-white p-2.5 shadow-card transition-all duration-300 hover:shadow-lg">
                                    <div class="relative overflow-hidden rounded-2xl aspect-[4/3] bg-surface-low">
                                        <img
                                            src="{{ asset('images/depot-hero.jpg') }}"
                                            alt="{{ __('Tim dan Fasilitas Depot Air Minum CV. Aufa') }}"
                                            class="size-full object-cover object-center"
                                            loading="eager"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-ink/60 via-transparent to-transparent"></div>

                                        {{-- Floating Status Badge --}}
                                        <div class="absolute top-3.5 left-3.5 flex items-center gap-2 rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold text-ink shadow-sm backdrop-blur-xs border border-line/50">
                                            <span class="size-2 rounded-full bg-success animate-pulse"></span>
                                            <span>{{ __('Depot Resmi CV. Aufa Padang') }}</span>
                                        </div>

                                        {{-- Floating Bottom Highlight --}}
                                        <div class="absolute bottom-3 inset-x-3 flex items-center justify-between rounded-xl bg-white/95 px-3.5 py-2 shadow-sm backdrop-blur-xs border border-line/60">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-primary">
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-bold text-ink">{{ __('Tim Resmi CV. Aufa') }}</div>
                                                    <div class="text-[11px] text-muted">{{ __('Siap melayani isi ulang & antar') }}</div>
                                                </div>
                                            </div>
                                            <span class="rounded-lg bg-success-soft px-2.5 py-1 text-xs font-semibold text-success">
                                                {{ __('Buka') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION LAYANAN & HARGA --}}
            <section id="harga" class="border-t border-line bg-white py-16 sm:py-20 scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="text-xs font-semibold tracking-wider text-primary uppercase">{{ __('Transparansi Tarif') }}</div>
                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                            Daftar Harga Resmi CV. Aufa
                        </h2>
                        <p class="mx-auto mt-3 max-w-2xl text-base text-muted">
                            Harga seragam dan transparan di seluruh depot resmi CV. Aufa. Tanpa biaya tersembunyi.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-6 grid-cols-1 {{ $hargaGalonBaru !== null ? 'md:grid-cols-3 max-w-7xl' : 'md:grid-cols-2 max-w-4xl' }} mx-auto items-stretch">
                        {{-- Kartu Walk-in --}}
                        <div class="flex flex-col justify-between rounded-3xl border border-line bg-surface p-7 shadow-2xs transition hover:shadow-card">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="rounded-full bg-surface-low px-3 py-1 text-xs font-semibold text-muted">
                                        {{ __('Beli di Depot') }}
                                    </span>
                                    <span class="text-xs text-muted">Walk-in</span>
                                </div>

                                <h3 class="mt-4 text-xl font-bold text-ink">Isi Ulang Walk-in</h3>
                                <p class="mt-2 text-sm text-muted">
                                    Bawa galon kosong Anda dan isi langsung di depot cabang resmi CV. Aufa terdekat.
                                </p>

                                <div class="mt-6 flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-primary" data-test="price-walk-in">
                                        Rp{{ number_format($hargaWalkIn, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm font-medium text-muted">/ galon</span>
                                </div>

                                <ul class="mt-6 space-y-2.5 text-xs text-muted">
                                    <li class="flex items-center gap-2">
                                        <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Proses pengisian langsung di tempat</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Pembayaran Tunai & QR Statis di Kasir</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Tersedia di seluruh cabang aktif</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-8">
                                <a
                                    href="#lokasi-cabang"
                                    class="block w-full rounded-xl border border-line bg-white py-3 text-center text-sm font-semibold text-ink transition hover:bg-surface-low"
                                >
                                    {{ __('Cari Depot Terdekat') }}
                                </a>
                            </div>
                        </div>

                        {{-- Kartu COD / Antar-Jemput --}}
                        <div class="relative flex flex-col justify-between rounded-3xl border-2 border-primary bg-white p-7 shadow-card">
                            <div class="absolute -top-3 right-6 rounded-full bg-primary px-3 py-0.5 text-xs font-semibold text-white">
                                {{ __('Layanan Utama') }}
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold text-primary">
                                        {{ __('Antar ke Alamat') }}
                                    </span>
                                    <span class="text-xs text-muted">COD / Delivery</span>
                                </div>

                                <h3 class="mt-4 text-xl font-bold text-ink">Isi Ulang COD (Antar)</h3>
                                <p class="mt-2 text-sm text-muted">
                                    Pesan dari rumah, kurir kami menjemput galon kosong dan mengantar galon isi ulang langsung ke pintu Anda.
                                </p>

                                <div class="mt-6 flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-primary" data-test="price-cod">
                                        Rp{{ number_format($hargaCod, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm font-medium text-muted">/ galon</span>
                                </div>

                                <ul class="mt-6 space-y-2.5 text-xs text-muted">
                                    <li class="flex items-center gap-2">
                                        <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Diantar langsung ke alamat Anda</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Bayar saat air galon tiba (Tunai / QR)</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>Praktis tanpa perlu keluar rumah</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-8">
                                <a
                                    href="{{ $whatsappUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="block w-full rounded-xl bg-primary py-3 text-center text-sm font-semibold text-white shadow-xs transition hover:bg-primary-strong"
                                    data-test="card-cta-cod"
                                >
                                    {{ __('Pesan Antar via WhatsApp') }}
                                </a>
                            </div>
                        </div>

                        {{-- Kartu Galon Kosong Baru (tampil bila config sigalon.harga.galon_baru terisi; nilai masih sementara, PRD §6) --}}
                        @if($hargaGalonBaru !== null)
                            <div class="flex flex-col justify-between rounded-3xl border border-line bg-surface p-7 shadow-2xs transition hover:shadow-card" data-test="card-galon-baru">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="rounded-full bg-warning-soft px-3 py-1 text-xs font-semibold text-warning">
                                            {{ __('Khusus Walk-in') }}
                                        </span>
                                        <span class="text-xs text-muted">Galon Baru</span>
                                    </div>

                                    <h3 class="mt-4 text-xl font-bold text-ink">Galon Kosong Baru</h3>
                                    <p class="mt-2 text-sm text-muted">
                                        Beli galon kosong baru resmi CV. Aufa. Tersedia khusus pembelian langsung di depot walk-in.
                                    </p>

                                    <div class="mt-6 flex items-baseline gap-1">
                                        <span class="text-3xl font-bold text-ink" data-test="price-galon-baru">
                                            Rp{{ number_format($hargaGalonBaru, 0, ',', '.') }}
                                        </span>
                                        <span class="text-sm font-medium text-muted">/ unit</span>
                                    </div>

                                    <ul class="mt-6 space-y-2.5 text-xs text-muted">
                                        <li class="flex items-center gap-2">
                                            <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span>Galon standar resmi & higienis CV. Aufa</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <svg class="size-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span>Material food-grade, tebal, & tahan lama</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <svg class="size-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            <span>Hanya dapat dibeli walk-in di depot</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="mt-8 space-y-2">
                                    <a
                                        href="#lokasi-cabang"
                                        class="block w-full rounded-xl border border-line bg-white py-3 text-center text-sm font-semibold text-ink transition hover:bg-surface-low"
                                    >
                                        {{ __('Cari Depot Terdekat') }}
                                    </a>
                                    <a
                                        href="{{ $whatsappGalonBaruUrl ?? $whatsappUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="block w-full text-center text-xs font-semibold text-primary hover:underline py-1"
                                    >
                                        {{ __('Tanya Stok Galon via WhatsApp') }} &rarr;
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- SECTION CARA PESAN --}}
            <section id="cara-pesan" class="border-t border-line bg-surface py-16 sm:py-20 scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="text-xs font-semibold tracking-wider text-primary uppercase">{{ __('Alur Pemesanan') }}</div>
                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                            3 Langkah Mudah Pemesanan Air Galon
                        </h2>
                        <p class="mx-auto mt-3 max-w-2xl text-base text-muted">
                            Proses pemesanan praktis dan transparan untuk pelanggan di seluruh area Kota Padang.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-8 md:grid-cols-3 max-w-5xl mx-auto">
                        {{-- Langkah 1 --}}
                        <div class="relative rounded-3xl border border-line bg-white p-7 shadow-card">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-primary text-xl font-bold text-white shadow-xs">
                                1
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-ink">Pilih Cabang Terdekat</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">
                                Temukan lokasi depot CV. Aufa yang paling dekat dengan wilayah Anda melalui peta interaktif cabang di bawah ini.
                            </p>
                        </div>

                        {{-- Langkah 2 --}}
                        <div class="relative rounded-3xl border border-line bg-white p-7 shadow-card">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-primary text-xl font-bold text-white shadow-xs">
                                2
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-ink">Hubungi via WhatsApp</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">
                                Kirim pesan pemesanan ke admin WhatsApp kami. Sebutkan nama, alamat pengantaran, dan jumlah galon yang ingin dipesan.
                            </p>
                        </div>

                        {{-- Langkah 3 --}}
                        <div class="relative rounded-3xl border border-line bg-white p-7 shadow-card">
                            <div class="flex size-12 items-center justify-center rounded-2xl bg-primary text-xl font-bold text-white shadow-xs">
                                3
                            </div>
                            <h3 class="mt-5 text-lg font-bold text-ink">Antar & Bayar di Tempat</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">
                                Kurir depot mengantarkan air galon ke alamat Anda. Lakukan pembayaran saat pesanan tiba menggunakan uang tunai atau QR statis.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECTION PETA LOKASI CABANG --}}
            <section id="lokasi-cabang" class="border-t border-line bg-white py-16 sm:py-20 scroll-mt-20">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <div class="text-xs font-semibold tracking-wider text-primary uppercase">{{ __('Jaringan Depot CV. Aufa') }}</div>
                        <h2 class="mt-2 text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                            Lokasi Depot Cabang di Kota Padang
                        </h2>
                        <p class="mx-auto mt-3 max-w-2xl text-base text-muted">
                            Kunjungi langsung atau temukan depot terdekat yang siap melayani pengantaran air galon ke wilayah tempat tinggal Anda.
                        </p>
                    </div>

                    <div class="mt-12 grid gap-8 lg:grid-cols-12 items-start">
                        {{-- Kontainer Peta Leaflet --}}
                        <div class="lg:col-span-8">
                            <div class="relative z-0 isolate overflow-hidden rounded-3xl border border-line bg-surface-low shadow-card">
                                <div
                                    id="branch-map"
                                    class="relative z-0 h-[480px] w-full"
                                    data-branches="{{ json_encode($branches) }}"
                                ></div>

                                {{-- Fallback jika JavaScript nonaktif atau data kosong --}}
                                @if($branches->isEmpty())
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-surface-low p-6 text-center">
                                        <svg class="size-12 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <div class="mt-3 font-semibold text-ink">{{ __('Belum ada cabang aktif yang terdaftar') }}</div>
                                        <p class="mt-1 text-sm text-muted">Informasi titik koordinat depot cabang sedang dalam penyesuaian data mitra.</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs text-muted px-2">
                                <span>* Klik pada marker peta untuk melihat nama dan alamat cabang</span>
                                <span>Data peta &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="underline hover:text-primary">OpenStreetMap</a> contributors</span>
                            </div>
                        </div>

                        {{-- Panel Daftar Cabang --}}
                        <div class="lg:col-span-4">
                            <div class="rounded-3xl border border-line bg-surface p-6 shadow-2xs">
                                <div class="flex items-center justify-between border-b border-line pb-4">
                                    <div class="font-bold text-ink">Daftar Depot Cabang</div>
                                    <span class="rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-semibold text-primary" data-test="branch-count">
                                        {{ $branches->count() }} Cabang
                                    </span>
                                </div>

                                <div class="mt-4 space-y-3 max-h-[420px] overflow-y-auto pr-1">
                                    @forelse($branches as $branch)
                                        <div
                                            class="group cursor-pointer rounded-2xl border border-line bg-white p-4 shadow-2xs transition hover:border-primary hover:bg-primary-soft/30"
                                            onclick="focusBranchMarker({{ (float) $branch->latitude }}, {{ (float) $branch->longitude }}, '{{ addslashes($branch->nama) }}')"
                                            data-test="branch-card-{{ $branch->id }}"
                                        >
                                            <div class="flex items-start justify-between">
                                                <h4 class="font-semibold text-ink group-hover:text-primary text-sm">
                                                    {{ $branch->nama }}
                                                </h4>
                                                <span class="size-2 rounded-full bg-success shrink-0 mt-1.5" title="Cabang Aktif"></span>
                                            </div>
                                            <p class="mt-1.5 text-xs text-muted leading-relaxed">
                                                {{ $branch->alamat }}
                                            </p>
                                            <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-primary">
                                                <span>Fokuskan di Peta</span>
                                                <svg class="size-3 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-8 text-center text-sm text-muted">
                                            {{ __('Belum ada cabang yang aktif.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        {{-- FOOTER --}}
        <footer class="border-t border-line bg-white py-12 text-sm text-muted">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <x-app-logo />
                    </div>

                    <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-medium">
                        <a href="#hero" class="transition hover:text-primary">{{ __('Beranda') }}</a>
                        <a href="#harga" class="transition hover:text-primary">{{ __('Layanan & Harga') }}</a>
                        <a href="#cara-pesan" class="transition hover:text-primary">{{ __('Cara Pesan') }}</a>
                        <a href="#lokasi-cabang" class="transition hover:text-primary">{{ __('Lokasi Cabang') }}</a>
                        <a href="{{ route('login') }}" class="text-primary hover:underline">{{ __('Portal Masuk Staf') }}</a>
                    </div>
                </div>

                <div class="mt-8 border-t border-line pt-6 text-center text-xs text-muted">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'SIGALON') }} — CV. Aufa. Digitalisasi Distribusi Air Minum Galon. Seluruh hak cipta dilindungi.</p>
                </div>
            </div>
        </footer>

        {{-- Leaflet JS --}}
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>

        <script>
            let sigalonMap = null;
            const branchMarkers = {};

            document.addEventListener('DOMContentLoaded', function () {
                const mapEl = document.getElementById('branch-map');
                if (!mapEl || typeof L === 'undefined') {
                    return;
                }

                let branches = [];
                try {
                    branches = JSON.parse(mapEl.getAttribute('data-branches') || '[]');
                } catch (e) {
                    console.error('Gagal membaca data cabang:', e);
                }

                // Default pusat Kota Padang
                const defaultCenter = [-0.9471, 100.4172];
                sigalonMap = L.map('branch-map', {
                    scrollWheelZoom: false,
                }).setView(defaultCenter, 12);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors'
                }).addTo(sigalonMap);

                if (branches.length > 0) {
                    const boundsGroup = [];

                    branches.forEach(function (branch) {
                        const lat = parseFloat(branch.latitude);
                        const lng = parseFloat(branch.longitude);

                        if (!isNaN(lat) && !isNaN(lng)) {
                            const marker = L.marker([lat, lng]).addTo(sigalonMap);

                            // Nama & alamat diisi lewat textContent agar input data cabang tidak pernah dirender sebagai HTML.
                            const popupContent = document.createElement('div');
                            popupContent.className = 'p-1 font-sans';

                            const popupLabel = document.createElement('div');
                            popupLabel.className = 'text-[11px] font-semibold text-primary uppercase tracking-wider';
                            popupLabel.textContent = 'Depot CV. Aufa';

                            const popupName = document.createElement('div');
                            popupName.className = 'text-sm font-bold text-ink mt-0.5';
                            popupName.textContent = branch.nama ?? '';

                            const popupAddress = document.createElement('div');
                            popupAddress.className = 'text-xs text-muted mt-1 leading-snug';
                            popupAddress.textContent = branch.alamat ?? '';

                            popupContent.append(popupLabel, popupName, popupAddress);

                            marker.bindPopup(popupContent);
                            const key = `${lat.toFixed(5)}_${lng.toFixed(5)}`;
                            branchMarkers[key] = marker;
                            boundsGroup.push([lat, lng]);
                        }
                    });

                    if (boundsGroup.length > 0) {
                        sigalonMap.fitBounds(boundsGroup, {
                            padding: [50, 50],
                            maxZoom: 15
                        });
                    }
                }
            });

            function focusBranchMarker(lat, lng, name) {
                if (!sigalonMap) return;

                const latNum = parseFloat(lat);
                const lngNum = parseFloat(lng);
                if (isNaN(latNum) || isNaN(lngNum)) return;

                sigalonMap.flyTo([latNum, lngNum], 15, {
                    animate: true,
                    duration: 1.2
                });

                const key = `${latNum.toFixed(5)}_${lngNum.toFixed(5)}`;
                if (branchMarkers[key]) {
                    branchMarkers[key].openPopup();
                }

                const mapSection = document.getElementById('lokasi-cabang');
                if (mapSection) {
                    mapSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
        </script>
    </body>
</html>
