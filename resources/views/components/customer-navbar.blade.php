@props([
    'whatsappUrl' => null,
])

@php
    $defaultPhone = preg_replace('/\D+/', '', (string) config('services.admin_whatsapp_number', '6281200000000'));
    $resolvedWhatsappUrl = $whatsappUrl ?? ('https://wa.me/'.$defaultPhone.'?text='.rawurlencode('Halo CV. Aufa, saya ingin memesan air galon SIGALON.'));
@endphp

<header class="sticky top-0 z-50 w-full border-b border-line bg-white/95 backdrop-blur-sm shadow-xs" id="customer-navbar">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <x-app-logo />
            </a>

            <nav class="hidden md:flex items-center gap-6 text-sm" id="desktop-nav">
                <a
                    href="{{ route('home') }}#hero"
                    data-nav-section="hero"
                    aria-current="page"
                    class="border-b-2 border-primary py-1 font-semibold text-primary transition-colors duration-150"
                >
                    {{ __('Beranda') }}
                </a>
                <a
                    href="#harga"
                    data-nav-section="harga"
                    class="border-b-2 border-transparent py-1 font-medium text-muted transition-colors duration-150 hover:text-primary"
                >
                    {{ __('Layanan & Harga') }}
                </a>
                <a
                    href="#cara-pesan"
                    data-nav-section="cara-pesan"
                    class="border-b-2 border-transparent py-1 font-medium text-muted transition-colors duration-150 hover:text-primary"
                >
                    {{ __('Cara Pesan') }}
                </a>
                <a
                    href="#lokasi-cabang"
                    data-nav-section="lokasi-cabang"
                    class="border-b-2 border-transparent py-1 font-medium text-muted transition-colors duration-150 hover:text-primary"
                >
                    {{ __('Lokasi Cabang') }}
                </a>
                <a
                    href="{{ $resolvedWhatsappUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 py-1 font-medium text-muted transition-colors duration-150 hover:text-primary"
                >
                    <svg class="size-4 text-success" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.101.005.24.038.376.365.144.346.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.679.116-.174.231-.145.39-.087s1.011.477 1.184.564c.174.087.289.13.332.203.044.072.044.419-.1 1.024z"/></svg>
                    <span>{{ __('Bantuan WhatsApp') }}</span>
                </a>
            </nav>
        </div>

        <div class="hidden md:flex items-center gap-3">
            <a
                href="{{ $resolvedWhatsappUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-primary-bright"
                data-test="navbar-cta-order"
            >
                <span>{{ __('Pesan Air Galon') }}</span>
            </a>

            @auth
                @if(Auth::user()->isPelanggan())
                    @php $pelanggan = Auth::user(); @endphp
                    <div class="flex items-center gap-3 text-sm">
                        {{-- Avatar: foto Google atau inisial --}}
                        <div class="relative group">
                            @if($pelanggan->googleAvatarUrl())
                                <img
                                    src="{{ $pelanggan->googleAvatarUrl() }}"
                                    alt="{{ $pelanggan->name }}"
                                    title="{{ $pelanggan->name }}"
                                    class="size-9 rounded-full object-cover ring-2 ring-primary/30 shadow-sm cursor-default select-none"
                                    referrerpolicy="no-referrer"
                                    data-test="customer-avatar-google"
                                >
                            @else
                                <div
                                    title="{{ $pelanggan->name }}"
                                    class="size-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm ring-2 ring-primary/30 shadow-sm cursor-default select-none"
                                    data-test="customer-avatar-initial"
                                >
                                    {{ $pelanggan->avatarInitial() }}
                                </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="text-sm font-medium text-danger hover:underline cursor-pointer"
                                data-test="customer-logout-button"
                            >
                                {{ __('Keluar') }}
                            </button>
                        </form>
                    </div>
                @else
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-lg bg-surface-low px-4 py-2 text-sm font-semibold text-primary hover:bg-primary-soft"
                    >
                        {{ __('Dasbor Staf') }}
                    </a>
                @endif
            @else
                <a
                    href="{{ route('customer.login') }}"
                    class="rounded-lg border border-line bg-white px-4 py-2 text-sm font-semibold text-ink shadow-2xs transition hover:bg-surface-low"
                    data-test="customer-login-link"
                >
                    {{ __('Masuk / Daftar') }}
                </a>
            @endauth
        </div>

        <!-- Mobile Menu Toggle Button -->
        <div class="flex md:hidden items-center gap-2">
            <button
                type="button"
                id="mobile-menu-toggle-btn"
                class="rounded-lg p-2 text-muted hover:bg-surface-low hover:text-ink focus:outline-hidden"
                aria-label="{{ __('Buka menu') }}"
                aria-expanded="false"
            >
                <svg id="mobile-menu-icon-open" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="mobile-menu-icon-close" class="size-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobile-dropdown-menu" class="hidden border-t border-line bg-white px-4 py-4 md:hidden space-y-3">
        <a
            href="{{ route('home') }}#hero"
            data-nav-section="hero"
            data-nav-mobile
            aria-current="page"
            class="block border-b-2 border-primary py-1.5 text-sm font-semibold text-primary transition-colors duration-150"
        >
            {{ __('Beranda') }}
        </a>
        <a
            href="#harga"
            data-nav-section="harga"
            data-nav-mobile
            class="block border-b-2 border-transparent py-1.5 text-sm font-medium text-ink transition-colors duration-150 hover:text-primary"
        >
            {{ __('Layanan & Harga') }}
        </a>
        <a
            href="#cara-pesan"
            data-nav-section="cara-pesan"
            data-nav-mobile
            class="block border-b-2 border-transparent py-1.5 text-sm font-medium text-ink transition-colors duration-150 hover:text-primary"
        >
            {{ __('Cara Pesan') }}
        </a>
        <a
            href="#lokasi-cabang"
            data-nav-section="lokasi-cabang"
            data-nav-mobile
            class="block border-b-2 border-transparent py-1.5 text-sm font-medium text-ink transition-colors duration-150 hover:text-primary"
        >
            {{ __('Lokasi Cabang') }}
        </a>
        <a
            href="{{ $resolvedWhatsappUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="block py-1 text-sm font-medium text-success hover:underline"
        >
            {{ __('Bantuan WhatsApp') }}
        </a>
        <div class="pt-3 border-t border-line flex flex-col gap-2">
            <a
                href="{{ $resolvedWhatsappUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="w-full text-center rounded-lg bg-primary py-2 text-sm font-semibold text-white shadow-xs"
            >
                {{ __('Pesan Air Galon') }}
            </a>
            @auth
                @if(Auth::user()->isPelanggan())
                    @php $pelangganMobile = Auth::user(); @endphp
                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                            @if($pelangganMobile->googleAvatarUrl())
                                <img
                                    src="{{ $pelangganMobile->googleAvatarUrl() }}"
                                    alt="{{ $pelangganMobile->name }}"
                                    class="size-8 rounded-full object-cover ring-2 ring-primary/20"
                                    referrerpolicy="no-referrer"
                                >
                            @else
                                <div class="size-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs ring-2 ring-primary/20">
                                    {{ $pelangganMobile->avatarInitial() }}
                                </div>
                            @endif
                            <span class="text-sm font-medium text-ink">{{ $pelangganMobile->name }}</span>
                        </div>
                        <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-danger hover:underline">
                                {{ __('Keluar') }}
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="w-full text-center rounded-lg bg-surface-low py-2 text-sm font-semibold text-primary">
                        {{ __('Dasbor Staf') }}
                    </a>
                @endif
            @else
                <a href="{{ route('customer.login') }}" class="w-full text-center rounded-lg border border-line py-2 text-sm font-semibold text-ink">
                    {{ __('Masuk / Daftar') }}
                </a>
            @endauth
        </div>
    </div>
</header>

<script>
    (function () {
        function initNavbarActiveState() {
            const sections = ['hero', 'harga', 'cara-pesan', 'lokasi-cabang'];
            const sectionEls = sections
                .map(function (id) { return document.getElementById(id); })
                .filter(Boolean);

            const navLinks = document.querySelectorAll('[data-nav-section]');

            // Handle mobile menu toggle button
            const mobileToggleBtn = document.getElementById('mobile-menu-toggle-btn');
            const mobileMenu = document.getElementById('mobile-dropdown-menu');
            const openIcon = document.getElementById('mobile-menu-icon-open');
            const closeIcon = document.getElementById('mobile-menu-icon-close');

            if (mobileToggleBtn && mobileMenu) {
                mobileToggleBtn.addEventListener('click', function () {
                    const isHidden = mobileMenu.classList.toggle('hidden');
                    mobileToggleBtn.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                    if (openIcon && closeIcon) {
                        if (isHidden) {
                            openIcon.classList.remove('hidden');
                            closeIcon.classList.add('hidden');
                        } else {
                            openIcon.classList.add('hidden');
                            closeIcon.classList.remove('hidden');
                        }
                    }
                });
            }

            // Jika dipanggil di halaman selain landing page (tanpa seksi-seksi di atas),
            // nonaktifkan active state agar aman dan tidak menimbulkan error.
            if (sectionEls.length === 0) {
                navLinks.forEach(function (link) {
                    link.removeAttribute('aria-current');
                    link.classList.remove('text-primary', 'font-semibold', 'border-primary');
                    const isMobile = link.hasAttribute('data-nav-mobile');
                    link.classList.add(isMobile ? 'text-ink' : 'text-muted', 'font-medium', 'border-transparent');
                });
                return;
            }

            let activeSection = null;
            let isClickScrolling = false;
            let scrollLockTimeout = null;

            function setActiveSection(id) {
                if (!id || activeSection === id) return;
                activeSection = id;

                navLinks.forEach(function (link) {
                    const isMatch = link.getAttribute('data-nav-section') === id;
                    const isMobile = link.hasAttribute('data-nav-mobile');

                    if (isMatch) {
                        link.setAttribute('aria-current', 'page');
                        link.classList.remove('text-muted', 'text-ink', 'font-medium', 'border-transparent');
                        link.classList.add('text-primary', 'font-semibold', 'border-primary');
                    } else {
                        link.removeAttribute('aria-current');
                        link.classList.remove('text-primary', 'font-semibold', 'border-primary');
                        link.classList.add(isMobile ? 'text-ink' : 'text-muted', 'font-medium', 'border-transparent');
                    }
                });
            }

            // Inisialisasi: periksa apakah ada hash di URL
            const initialHash = window.location.hash.replace('#', '');
            if (initialHash && sections.includes(initialHash)) {
                setActiveSection(initialHash);
            } else {
                setActiveSection('hero');
            }

            // Event klik pada tautan navigasi: kunci state aktif saat smooth-scroll
            navLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-nav-section');
                    if (!targetId) return;

                    setActiveSection(targetId);
                    isClickScrolling = true;

                    if (scrollLockTimeout) {
                        clearTimeout(scrollLockTimeout);
                    }

                    function unlockScroll() {
                        isClickScrolling = false;
                        window.removeEventListener('scrollend', unlockScroll);
                    }

                    window.addEventListener('scrollend', unlockScroll, { once: true });
                    scrollLockTimeout = setTimeout(unlockScroll, 1200);

                    // Tutup menu mobile jika sedang terbuka
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        if (mobileToggleBtn) mobileToggleBtn.setAttribute('aria-expanded', 'false');
                        if (openIcon) openIcon.classList.remove('hidden');
                        if (closeIcon) closeIcon.classList.add('hidden');
                    }
                });
            });

            // IntersectionObserver untuk scroll-spy otomatis
            const ratios = {};
            const observer = new IntersectionObserver(function (entries) {
                if (isClickScrolling) return;

                entries.forEach(function (entry) {
                    ratios[entry.target.id] = entry.isIntersecting ? entry.intersectionRatio : 0;
                });

                // Posisi paling atas: selalu aktifkan hero
                if (window.scrollY < 120) {
                    setActiveSection('hero');
                    return;
                }

                // Posisi paling bawah halaman: aktifkan lokasi-cabang
                const isBottom = (window.innerHeight + window.scrollY) >= (document.documentElement.scrollHeight - 60);
                if (isBottom && document.getElementById('lokasi-cabang')) {
                    setActiveSection('lokasi-cabang');
                    return;
                }

                // Pilih seksi dengan rasio tampilan terbesar
                let maxRatio = 0;
                let dominantId = null;
                sections.forEach(function (id) {
                    const r = ratios[id] || 0;
                    if (r > maxRatio) {
                        maxRatio = r;
                        dominantId = id;
                    }
                });

                if (dominantId && maxRatio > 0.05) {
                    setActiveSection(dominantId);
                }
            }, {
                root: null,
                rootMargin: '-70px 0px -40% 0px',
                threshold: [0, 0.1, 0.25, 0.5, 0.75, 1.0]
            });

            sectionEls.forEach(function (el) {
                observer.observe(el);
            });

            // Pantau perubahan hash via navigasi back/forward peramban
            window.addEventListener('hashchange', function () {
                const h = window.location.hash.replace('#', '');
                if (h && sections.includes(h)) {
                    setActiveSection(h);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initNavbarActiveState);
        } else {
            initNavbarActiveState();
        }
    })();
</script>
