<x-layouts::auth :title="__('Masuk Staf')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Portal Staf') }}</span>
            <flux:heading size="xl" level="1" class="text-ink!">{{ __('Masuk ke SIGALON') }}</flux:heading>
            <flux:text class="text-muted!">{{ __('Gunakan email dan kata sandi akun staf Anda.') }}</flux:text>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-5"
            x-data="{ submitting: false }"
            x-on:submit="submitting = true"
        >
            @csrf

            <flux:input
                name="email"
                :label="__('Email')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="nama@contoh.com"
            />

            <flux:input
                name="password"
                :label="__('Kata sandi')"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />

            <flux:checkbox name="remember" :label="__('Ingat saya')" :checked="old('remember')" />

            <flux:button
                variant="primary"
                type="submit"
                class="w-full"
                data-test="login-button"
                x-bind:disabled="submitting"
            >
                <span x-show="! submitting">{{ __('Masuk') }}</span>
                <span x-show="submitting" x-cloak>{{ __('Memproses…') }}</span>
            </flux:button>
        </form>

        <div class="flex flex-col gap-1 rounded-xl bg-surface-low p-4 text-sm text-muted">
            <p>{{ __('Lupa kata sandi? Hubungi Admin Cabang atau Super Admin untuk mengatur ulang.') }}</p>
            <p>{{ __('Kurir menggunakan aplikasi Android SIGALON.') }}</p>
        </div>
    </div>
</x-layouts::auth>
