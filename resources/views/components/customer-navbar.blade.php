<nav class="flex w-full items-center justify-between border-b border-line bg-white px-6 py-4 shadow-sm">
    <div class="flex items-center gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <x-app-logo />
        </a>
    </div>

    <div class="flex items-center gap-4">
        @auth
            @if(Auth::user()->isPelanggan())
                <div class="flex items-center gap-3 text-sm">
                    <span class="rounded-full bg-primary-soft px-3 py-1 font-medium text-primary">
                        {{ Auth::user()->name }}
                    </span>
                    <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="text-sm font-medium text-danger hover:underline"
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
                class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-bright"
                data-test="customer-login-link"
            >
                {{ __('Masuk / Daftar') }}
            </a>
        @endauth
    </div>
</nav>
