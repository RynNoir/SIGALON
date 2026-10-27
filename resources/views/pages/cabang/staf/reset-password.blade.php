<x-layouts::app :title="__('Reset Kata Sandi Staf')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 shadow-card">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary">
                <a href="{{ route('cabang.staf.index') }}" class="hover:underline" wire:navigate>{{ __('Kelola Staf') }}</a>
                <span>/</span>
                <span>{{ __('Reset Kata Sandi') }}</span>
            </div>
            <flux:heading size="xl" level="1" class="text-deep!">{{ __('Reset Kata Sandi Staf') }}</flux:heading>
            <flux:text class="text-muted!">
                {{ __('Atur ulang kata sandi baru untuk staf operasional cabang :cabang.', ['cabang' => $admin->cabang?->nama ?? '-']) }}
            </flux:text>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-card">
            <form
                method="POST"
                action="{{ route('cabang.staf.reset-password.store', $staf) }}"
                class="flex max-w-xl flex-col gap-6"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
            >
                @csrf

                <div class="rounded-xl border border-line bg-surface-low p-4">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Nama Staf') }}</span>
                            <p class="text-sm font-semibold text-ink">{{ $staf->name }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Email') }}</span>
                            <p class="text-sm font-semibold text-ink">{{ $staf->email }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Peran') }}</span>
                            <p class="text-sm font-semibold text-ink">{{ $staf->role->label() }}</p>
                        </div>
                    </div>
                </div>

                <flux:input
                    name="password"
                    :label="__('Kata Sandi Baru')"
                    type="password"
                    required
                    autofocus
                    placeholder="Minimal 8 karakter"
                    viewable
                    data-test="input-new-password"
                />

                <flux:input
                    name="password_confirmation"
                    :label="__('Konfirmasi Kata Sandi Baru')"
                    type="password"
                    required
                    placeholder="Ulangi kata sandi baru"
                    viewable
                    data-test="input-confirm-password"
                />

                <div class="flex items-center gap-3 pt-2">
                    <flux:button
                        variant="primary"
                        type="submit"
                        data-test="submit-reset-password-button"
                        x-bind:disabled="submitting"
                    >
                        <span x-show="! submitting">{{ __('Reset Kata Sandi') }}</span>
                        <span x-show="submitting" x-cloak>{{ __('Menyimpan…') }}</span>
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        :href="route('cabang.staf.index')"
                        wire:navigate
                    >
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
