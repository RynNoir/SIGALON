<x-layouts::app :title="__('Reset Password Admin Cabang')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 shadow-card">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary">
                <a href="{{ route('admin.admin-cabang.index') }}" class="hover:underline" wire:navigate>{{ __('Admin Cabang') }}</a>
                <span>/</span>
                <span>{{ __('Reset Password') }}</span>
            </div>
            <flux:heading size="xl" level="1" class="text-deep!">{{ __('Reset Kata Sandi Admin Cabang') }}</flux:heading>
            <flux:text class="text-muted!">
                {{ __('Atur ulang kata sandi staf :name (:email) secara langsung.', ['name' => $user->name, 'email' => $user->email]) }}
            </flux:text>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-card">
            <div class="mb-6 rounded-xl bg-surface-low p-4 text-sm">
                <span class="font-semibold text-ink">{{ __('Informasi Akun:') }}</span>
                <div class="mt-1 flex flex-col gap-0.5 text-muted">
                    <p><strong class="text-ink">{{ __('Nama:') }}</strong> {{ $user->name }}</p>
                    <p><strong class="text-ink">{{ __('Email:') }}</strong> {{ $user->email }}</p>
                    <p><strong class="text-ink">{{ __('Cabang:') }}</strong> {{ $user->cabang?->nama ?? '-' }}</p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.admin-cabang.reset-password.store', $user) }}"
                class="flex max-w-xl flex-col gap-6"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
            >
                @csrf

                <flux:input
                    name="password"
                    :label="__('Kata Sandi Baru')"
                    type="password"
                    required
                    autofocus
                    placeholder="Minimal 8 karakter"
                    viewable
                    data-test="input-reset-password"
                />

                <flux:input
                    name="password_confirmation"
                    :label="__('Konfirmasi Kata Sandi Baru')"
                    type="password"
                    required
                    placeholder="Ulangi kata sandi baru"
                    viewable
                    data-test="input-reset-password-confirmation"
                />

                <div class="flex items-center gap-3 pt-2">
                    <flux:button
                        variant="primary"
                        type="submit"
                        data-test="submit-reset-password-button"
                        x-bind:disabled="submitting"
                    >
                        <span x-show="! submitting">{{ __('Simpan Kata Sandi') }}</span>
                        <span x-show="submitting" x-cloak>{{ __('Menyimpan…') }}</span>
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        :href="route('admin.admin-cabang.index')"
                        wire:navigate
                    >
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
