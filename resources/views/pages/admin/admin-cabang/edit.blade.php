<x-layouts::app :title="__('Ubah Admin Cabang')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 shadow-card">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary">
                <a href="{{ route('admin.admin-cabang.index') }}" class="hover:underline" wire:navigate>{{ __('Admin Cabang') }}</a>
                <span>/</span>
                <span>{{ __('Ubah Akun') }}</span>
            </div>
            <flux:heading size="xl" level="1" class="text-deep!">{{ __('Ubah Akun Admin Cabang') }}</flux:heading>
            <flux:text class="text-muted!">
                {{ __('Perbarui data profil dan cabang penempatan staf :name.', ['name' => $user->name]) }}
            </flux:text>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-card">
            <form
                method="POST"
                action="{{ route('admin.admin-cabang.update', $user) }}"
                class="flex max-w-xl flex-col gap-6"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
            >
                @csrf
                @method('PUT')

                <flux:input
                    name="name"
                    :label="__('Nama Lengkap')"
                    :value="old('name', $user->name)"
                    type="text"
                    required
                    autofocus
                    placeholder="Contoh: Budi Santoso"
                    data-test="input-name-admin"
                />

                <flux:input
                    name="email"
                    :label="__('Alamat Email')"
                    :value="old('email', $user->email)"
                    type="email"
                    required
                    placeholder="admin.cabang@sigalon.id"
                    data-test="input-email-admin"
                />

                <div class="flex flex-col gap-1.5">
                    <label for="cabang_id" class="text-sm font-medium text-ink">
                        {{ __('Cabang Penempatan') }} <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="cabang_id"
                        name="cabang_id"
                        required
                        class="w-full rounded-xl border border-line bg-white px-3.5 py-2.5 text-sm text-ink transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        data-test="select-cabang-admin"
                    >
                        <option value="">{{ __('-- Pilih Cabang --') }}</option>
                        @foreach ($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" @selected(old('cabang_id', $user->cabang_id) == $cabang->id)>
                                {{ $cabang->nama }}
                                @unless ($cabang->is_active)
                                    ({{ __('Nonaktif') }})
                                @endunless
                            </option>
                        @endforeach
                    </select>
                    @error('cabang_id')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <flux:button
                        variant="primary"
                        type="submit"
                        data-test="submit-admin-button"
                        x-bind:disabled="submitting"
                    >
                        <span x-show="! submitting">{{ __('Perbarui Data') }}</span>
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
