<x-layouts::auth :title="__('Terlalu banyak percobaan')">
    <div class="flex flex-col gap-4" role="alert">
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-warning">{{ __('Akses dibatasi sementara') }}</span>
            <flux:heading size="xl" level="1" class="text-ink!">{{ __('Terlalu banyak percobaan') }}</flux:heading>
            <flux:text class="text-muted!">{{ __('Demi keamanan, coba lagi dalam 1 menit.') }}</flux:text>
        </div>

        <flux:button variant="primary" :href="url()->previous(route('home'))" class="w-full">
            {{ __('Kembali') }}
        </flux:button>
    </div>
</x-layouts::auth>
