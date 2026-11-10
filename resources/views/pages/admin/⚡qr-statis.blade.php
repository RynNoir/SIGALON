<?php

use App\Models\User;
use App\Services\QrCodeService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('Gambar QR Statis')]
class extends Component {
    use WithFileUploads;

    public ?TemporaryUploadedFile $gambar = null;

    public function mount(): void
    {
        $this->authorizeSuperAdmin();
    }

    public function hydrate(): void
    {
        $this->authorizeSuperAdmin();
    }

    #[Computed]
    public function qrUrl(): ?string
    {
        return app(QrCodeService::class)->getQrCodeUrl();
    }

    public function simpan(): void
    {
        $this->validate(
            ['gambar' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048']],
            [
                'gambar.required' => __('Pilih gambar QR terlebih dahulu.'),
                'gambar.image' => __('File harus berupa gambar PNG, JPG, atau WEBP.'),
                'gambar.mimes' => __('File harus berupa gambar PNG, JPG, atau WEBP.'),
                'gambar.max' => __('Ukuran gambar maksimal 2 MB.'),
            ],
        );

        $sudahAda = $this->qrUrl !== null;

        app(QrCodeService::class)->replaceQrCode($this->gambar);

        $this->reset('gambar');
        unset($this->qrUrl);

        Flux::toast(variant: 'success', text: $sudahAda ? __('Gambar QR berhasil diganti.') : __('Gambar QR berhasil diunggah.'));
    }

    /**
     * Hanya Super Admin yang mengelola gambar QR (PRD §7.D); diperiksa ulang di setiap request.
     */
    private function authorizeSuperAdmin(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->isSuperAdmin(), 403);
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 shadow-card">
        <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Pembayaran') }}</span>
        <flux:heading size="xl" level="1" class="text-deep!">{{ __('Gambar QR Statis') }}</flux:heading>
        <flux:text class="text-muted!">
            {{ __('Gambar ini tampil di halaman pemesanan saat pelanggan memilih bayar dengan QR. Pembayaran QR tidak terverifikasi otomatis; kurir yang mengonfirmasi bukti bayar.') }}
        </flux:text>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- QR yang sedang dipakai --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card">
            <flux:heading size="lg">{{ __('QR yang dipakai sekarang') }}</flux:heading>

            @if ($this->qrUrl !== null)
                <img
                    src="{{ $this->qrUrl }}"
                    alt="{{ __('Gambar QR statis pembayaran') }}"
                    class="mx-auto w-full max-w-64 rounded-xl border border-line bg-white p-3"
                    data-test="qr-saat-ini"
                />
            @else
                <div class="flex flex-col items-center justify-center rounded-xl bg-surface-low py-10 text-center" data-test="qr-kosong">
                    <flux:icon.qr-code class="size-10 text-muted" />
                    <flux:heading class="mt-3">{{ __('Belum ada gambar QR') }}</flux:heading>
                    <flux:text class="mt-1 text-muted!">{{ __('Pelanggan yang memilih QR akan diminta memilih Tunai atau menghubungi depot.') }}</flux:text>
                </div>
            @endif
        </div>

        {{-- Form unggah --}}
        <form wire:submit="simpan" class="flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-card">
            <flux:heading size="lg">{{ $this->qrUrl !== null ? __('Ganti gambar QR') : __('Unggah gambar QR') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('File gambar') }}</flux:label>
                <flux:description>{{ __('PNG, JPG, atau WEBP, maksimal 2 MB. Gambar baru langsung menggantikan gambar lama.') }}</flux:description>
                <flux:input type="file" wire:model="gambar" accept="image/png,image/jpeg,image/webp" data-test="input-gambar" />
                <flux:error name="gambar" />
            </flux:field>

            <div class="flex items-center gap-2 text-sm text-muted" wire:loading.flex wire:target="gambar" role="status">
                <flux:icon.arrow-path variant="mini" class="animate-spin" />
                {{ __('Mengunggah file…') }}
            </div>

            <div>
                <flux:button type="submit" variant="primary" icon="arrow-up-tray" wire:loading.attr="disabled" wire:target="gambar,simpan" data-test="simpan-qr">
                    {{ __('Simpan Gambar QR') }}
                </flux:button>
            </div>
        </form>
    </div>
</section>
