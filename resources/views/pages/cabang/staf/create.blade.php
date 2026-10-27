<x-layouts::app :title="__('Tambah Staf Cabang')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 shadow-card">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary">
                <a href="{{ route('cabang.staf.index') }}" class="hover:underline" wire:navigate>{{ __('Kelola Staf') }}</a>
                <span>/</span>
                <span>{{ __('Tambah Staf') }}</span>
            </div>
            <flux:heading size="xl" level="1" class="text-deep!">{{ __('Tambah Staf Cabang') }}</flux:heading>
            <flux:text class="text-muted!">
                {{ __('Buat akun staf baru untuk operasional cabang :cabang.', ['cabang' => $admin->cabang?->nama ?? '-']) }}
            </flux:text>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-card">
            <form
                method="POST"
                action="{{ route('cabang.staf.store') }}"
                class="flex max-w-xl flex-col gap-6"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
            >
                @csrf

                <div class="rounded-xl border border-line bg-surface-low p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Cabang Penempatan') }}</span>
                    <p class="mt-0.5 text-sm font-semibold text-ink">{{ $admin->cabang?->nama ?? '-' }}</p>
                    <p class="text-xs text-muted">{{ __('Staf baru otomatis ditugaskan pada cabang aktif Anda.') }}</p>
                </div>

                <flux:input
                    name="name"
                    :label="__('Nama Lengkap')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    placeholder="Contoh: Rian Pratama"
                    data-test="input-name-staf"
                />

                <flux:input
                    name="email"
                    :label="__('Alamat Email')"
                    :value="old('email')"
                    type="email"
                    required
                    placeholder="rian.kasir@sigalon.id"
                    data-test="input-email-staf"
                />

                <flux:input
                    name="password"
                    :label="__('Kata Sandi')"
                    type="password"
                    required
                    placeholder="Minimal 8 karakter"
                    viewable
                    data-test="input-password-staf"
                />

                <div class="flex flex-col gap-1.5">
                    <label for="role" class="text-sm font-medium text-ink">
                        {{ __('Peran Staf') }} <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="w-full rounded-xl border border-line bg-white px-3.5 py-2.5 text-sm text-ink transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        data-test="select-role-staf"
                    >
                        <option value="">{{ __('-- Pilih Peran --') }}</option>
                        <option value="{{ \App\Enums\Role::Kasir->value }}" @selected(old('role') === \App\Enums\Role::Kasir->value)>
                            {{ __('Kasir (Website Staf)') }}
                        </option>
                        <option value="{{ \App\Enums\Role::Kurir->value }}" @selected(old('role') === \App\Enums\Role::Kurir->value)>
                            {{ __('Kurir (Aplikasi Android)') }}
                        </option>
                    </select>
                    @error('role')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <flux:button
                        variant="primary"
                        type="submit"
                        data-test="submit-staf-button"
                        x-bind:disabled="submitting"
                    >
                        <span x-show="! submitting">{{ __('Buat Akun Staf') }}</span>
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
