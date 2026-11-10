<?php

use App\Models\Cabang;
use App\Models\User;
use App\Services\QrCodeService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('Gambar QR Statis')]
class extends Component {
    use WithFileUploads;

    public ?int $cabangId = null;

    public ?TemporaryUploadedFile $gambar = null;

    public function mount(): void
    {
        $this->authorizeSuperAdmin();
    }

    public function hydrate(): void
    {
        $this->authorizeSuperAdmin();
    }

    /**
     * @return Collection<int, Cabang>
     */
    #[Computed]
    public function daftarCabang(): Collection
    {
        return Cabang::query()->orderBy('nama')->get(['id', 'nama', 'is_active']);
    }

    /**
     * URL QR tiap cabang; null bila cabang belum punya QR.
     *
     * @return array<int, string|null>
     */
    #[Computed]
    public function qrPerCabang(): array
    {
        $service = app(QrCodeService::class);

        return $this->daftarCabang
            ->mapWithKeys(fn (Cabang $cabang): array => [$cabang->id => $service->getQrCodeUrl($cabang)])
            ->all();
    }

    public function simpan(): void
    {
        $this->validate(
            [
                'cabangId' => ['required', 'integer', 'exists:cabang,id'],
                'gambar' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            ],
            [
                'cabangId.required' => __('Pilih cabang pemilik QR.'),
                'cabangId.exists' => __('Cabang tidak ditemukan.'),
                'gambar.required' => __('Pilih gambar QR terlebih dahulu.'),
                'gambar.image' => __('File harus berupa gambar PNG, JPG, atau WEBP.'),
                'gambar.mimes' => __('File harus berupa gambar PNG, JPG, atau WEBP.'),
                'gambar.max' => __('Ukuran gambar maksimal 2 MB.'),
            ],
        );

        $cabang = Cabang::query()->findOrFail($this->cabangId);
        $service = app(QrCodeService::class);
        $sudahAda = $service->hasQrCode($cabang);

        $service->replaceQrCode($cabang, $this->gambar);

        $this->reset('gambar');
        unset($this->qrPerCabang);

        Flux::toast(variant: 'success', text: $sudahAda
            ? __('Gambar QR :cabang berhasil diganti.', ['cabang' => $cabang->nama])
            : __('Gambar QR :cabang berhasil diunggah.', ['cabang' => $cabang->nama]));
    }

    /**
     * Hanya Super Admin yang mengelola gambar QR (PRD §4, §7.D); diperiksa ulang di setiap request.
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
        <flux:heading size="xl" level="1" class="text-deep!">{{ __('Gambar QR Statis per Cabang') }}</flux:heading>
        <flux:text class="text-muted!">
            {{ __('Setiap cabang memakai QR-nya sendiri. QR tampil di halaman pemesanan sesuai cabang yang dipilih pelanggan. Pembayaran QR tidak terverifikasi otomatis; kurir yang mengonfirmasi bukti bayar.') }}
        </flux:text>
    </div>

    @if ($this->daftarCabang->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl bg-white py-12 text-center shadow-card" data-test="cabang-kosong">
            <flux:icon.building-office-2 class="size-10 text-muted" />
            <flux:heading class="mt-3">{{ __('Belum ada cabang') }}</flux:heading>
            <flux:text class="mt-1 text-muted!">{{ __('Tambahkan cabang di menu Kelola Cabang sebelum mengunggah QR.') }}</flux:text>
        </div>
    @else
        {{-- Form unggah --}}
        <form wire:submit="simpan" class="flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-card">
            <flux:heading size="lg">{{ __('Unggah atau ganti QR cabang') }}</flux:heading>

            <div class="grid gap-5 md:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Cabang') }}</flux:label>
                    <flux:select wire:model.live="cabangId" :placeholder="__('Pilih cabang')" data-test="pilih-cabang">
                        @foreach ($this->daftarCabang as $cabang)
                            <flux:select.option wire:key="opsi-cabang-{{ $cabang->id }}" :value="$cabang->id">{{ $cabang->nama }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="cabangId" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('File gambar') }}</flux:label>
                    <flux:input type="file" wire:model="gambar" accept="image/png,image/jpeg,image/webp" data-test="input-gambar" />
                    <flux:description>{{ __('PNG, JPG, atau WEBP, maksimal 2 MB. Gambar baru langsung menggantikan QR lama cabang tersebut.') }}</flux:description>
                    <flux:error name="gambar" />
                </flux:field>
            </div>

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

        {{-- Status QR tiap cabang --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" data-test="daftar-qr-cabang">
            @foreach ($this->daftarCabang as $cabang)
                @php($url = $this->qrPerCabang[$cabang->id] ?? null)
                <div wire:key="qr-cabang-{{ $cabang->id }}" data-test="qr-cabang-{{ $cabang->id }}" @class([
                    'flex flex-col gap-3 rounded-2xl bg-white p-5 shadow-card',
                    'ring-2 ring-primary' => $cabangId === $cabang->id,
                ])>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold text-ink">{{ $cabang->nama }}</div>
                            @unless ($cabang->is_active)
                                <span class="text-xs text-muted">{{ __('Cabang nonaktif') }}</span>
                            @endunless
                        </div>
                        @if ($url !== null)
                            <span class="shrink-0 rounded-full bg-success-soft px-2.5 py-0.5 text-xs font-semibold text-success">{{ __('QR tersedia') }}</span>
                        @else
                            <span class="shrink-0 rounded-full bg-warning-soft px-2.5 py-0.5 text-xs font-semibold text-warning">{{ __('Belum ada QR') }}</span>
                        @endif
                    </div>

                    @if ($url !== null)
                        <img src="{{ $url }}" alt="{{ __('QR statis :cabang', ['cabang' => $cabang->nama]) }}" class="mx-auto w-full max-w-48 rounded-xl border border-line bg-white p-2" data-test="img-qr-cabang-{{ $cabang->id }}" />
                    @else
                        <div class="flex flex-col items-center justify-center rounded-xl bg-surface-low py-8 text-center">
                            <flux:icon.qr-code class="size-8 text-muted" />
                            <flux:text class="mt-2 text-muted!">{{ __('Pelanggan cabang ini akan diminta memilih Tunai sampai QR diunggah.') }}</flux:text>
                        </div>
                    @endif

                    <flux:button size="sm" wire:click="$set('cabangId', {{ $cabang->id }})" data-test="pilih-qr-cabang-{{ $cabang->id }}">
                        {{ $url !== null ? __('Ganti QR cabang ini') : __('Unggah QR cabang ini') }}
                    </flux:button>
                </div>
            @endforeach
        </div>
    @endif
</section>
