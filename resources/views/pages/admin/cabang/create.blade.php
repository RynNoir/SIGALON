<x-layouts::app :title="__('Tambah Cabang')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 shadow-card">
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-primary">
                <a href="{{ route('admin.cabang.index') }}" class="hover:underline" wire:navigate>{{ __('Kelola Cabang') }}</a>
                <span>/</span>
                <span>{{ __('Tambah Baru') }}</span>
            </div>
            <flux:heading size="xl" level="1" class="text-deep!">{{ __('Tambah Cabang') }}</flux:heading>
            <flux:text class="text-muted!">
                {{ __('Masukkan identitas cabang baru beserta alamat dan titik koordinat untuk peta.') }}
            </flux:text>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-card">
            <form
                method="POST"
                action="{{ route('admin.cabang.store') }}"
                class="flex max-w-xl flex-col gap-6"
                x-data="{ submitting: false }"
                x-on:submit="submitting = true"
            >
                @csrf

                <flux:input
                    name="nama"
                    :label="__('Nama Cabang')"
                    :value="old('nama')"
                    type="text"
                    required
                    autofocus
                    placeholder="Contoh: Cabang Panam"
                    data-test="input-nama-cabang"
                />

                <div class="flex flex-col gap-1.5">
                    <label for="alamat" class="text-sm font-medium text-ink">
                        {{ __('Alamat Lengkap') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        required
                        class="w-full rounded-xl border border-line bg-white px-3.5 py-2.5 text-sm text-ink transition placeholder:text-muted/60 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        placeholder="Contoh: Jl. HR Soebrantas No. 12, Pekanbaru"
                        data-test="input-alamat-cabang"
                    >{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <span class="text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input
                        name="latitude"
                        :label="__('Latitude')"
                        :value="old('latitude')"
                        type="text"
                        placeholder="Contoh: 0.4651234"
                        data-test="input-latitude-cabang"
                    />

                    <flux:input
                        name="longitude"
                        :label="__('Longitude')"
                        :value="old('longitude')"
                        type="text"
                        placeholder="Contoh: 101.4012345"
                        data-test="input-longitude-cabang"
                    />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <flux:button
                        variant="primary"
                        type="submit"
                        data-test="submit-cabang-button"
                        x-bind:disabled="submitting"
                    >
                        <span x-show="! submitting">{{ __('Simpan Cabang') }}</span>
                        <span x-show="submitting" x-cloak>{{ __('Menyimpan…') }}</span>
                    </flux:button>

                    <flux:button
                        variant="ghost"
                        :href="route('admin.cabang.index')"
                        wire:navigate
                    >
                        {{ __('Batal') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
