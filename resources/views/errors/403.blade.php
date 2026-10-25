<x-layouts::auth :title="__('Akses ditolak')">
    <div class="flex flex-col gap-4" role="alert">
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-danger">{{ __('403 · Akses ditolak') }}</span>
            <flux:heading size="xl" level="1" class="text-ink!">{{ __('Anda tidak memiliki akses ke halaman ini') }}</flux:heading>
            <flux:text class="text-muted!">{{ __('Halaman ini hanya untuk staf yang berwenang.') }}</flux:text>
        </div>

        <ul class="flex flex-col gap-1 rounded-xl bg-surface-low p-4 text-sm text-muted">
            <li>{{ __('Kurir menggunakan aplikasi Android SIGALON.') }}</li>
            <li>{{ __('Pelanggan masuk dan memesan lewat halaman pelanggan.') }}</li>
        </ul>

        <div class="flex flex-col gap-2 sm:flex-row">
            <flux:button variant="primary" :href="route('home')" class="w-full">
                {{ __('Ke beranda') }}
            </flux:button>
            <flux:button :href="route('login')" class="w-full">
                {{ __('Masuk sebagai staf') }}
            </flux:button>
        </div>
    </div>
</x-layouts::auth>
