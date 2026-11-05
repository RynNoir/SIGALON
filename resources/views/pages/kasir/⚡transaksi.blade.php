<?php

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\Role;
use App\Enums\StatusTransaksi; 
use App\Models\Cabang;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Transaksi Walk-in & Galon Baru')]
    class extends Component {
    /**
     * Jenis item yang dipilih: 'isi_ulang' atau 'galon_baru'.
     */
    public string $jenis_item = 'isi_ulang';

    /**
     * Jumlah galon yang ditransaksikan (bilangan bulat, minimal 1).
     */
    public int $jumlah_galon = 1;

    /**
     * Metode pembayaran: 'tunai' atau 'qr'.
     */
    public string $metode_bayar = 'tunai';

    /**
     * Field pengaman: jika input klien/browser mengirim nilai total atau cabang_id,
     * server akan mengabaikannya demi keamanan dan integritas data.
     */
    public ?int $browser_total = null;
    public ?int $browser_cabang_id = null;

    /**
     * Data konfirmasi transaksi yang baru saja berhasil disimpan.
     *
     * @var array{id: int, item: string, jumlah: int, harga_satuan: int, total: int, metode: string, tanggal: string}|null
     */
    public ?array $transaksiTerakhir = null;

    public function mount(): void
    {
        $this->authorizeKasir();
    }

    public function hydrate(): void
    {
        // Otorisasi diulang di setiap request Livewire, bukan hanya saat halaman pertama dibuka.
        $this->authorizeKasir();
    }

    /**
     * Memastikan hanya akun Kasir aktif yang boleh berinteraksi.
     * Super Admin, Admin Cabang, Kurir, Pelanggan, dan tamu ditolak (403).
     */
    private function authorizeKasir(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User && $user->role === Role::Kasir, 403);

        return $user;
    }

    /**
     * Memeriksa apakah cabang kasir tersedia dan berstatus aktif.
     */
    #[Computed]
    public function cabangAktif(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || $user->cabang_id === null) {
            return false;
        }

        return (bool) ($user->cabang && $user->cabang->is_active);
    }

    /**
     * Nama cabang tempat Kasir bertugas.
     */
    #[Computed]
    public function namaCabang(): string
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->cabang?->nama ?? __('Cabang Tidak Ditentukan');
    }

    /**
     * Harga satuan per galon dari konfigurasi resmi (config/sigalon.php).
     */
    #[Computed]
    public function hargaSatuan(): int
    {
        return match ($this->jenis_item) {
            JenisItem::GalonBaru->value => (int) config('sigalon.harga.galon_baru'),
            default => (int) config('sigalon.harga.walk_in'),
        };
    }

    /**
     * Total harga untuk preview di antarmuka (dihitung dinamis, bukan input klien).
     */
    #[Computed]
    public function totalHarga(): int
    {
        $jumlah = max(0, (int) $this->jumlah_galon);

        return $jumlah * $this->hargaSatuan;
    }

    public function pilihItem(string $item): void
    {
        if (in_array($item, [JenisItem::IsiUlang->value, JenisItem::GalonBaru->value], true)) {
            $this->jenis_item = $item;
        }
    }

    public function pilihMetode(string $metode): void
    {
        if (in_array($metode, [MetodePembayaran::Tunai->value, MetodePembayaran::Qr->value], true)) {
            $this->metode_bayar = $metode;
        }
    }

    public function tambahJumlah(int $delta = 1): void
    {
        $this->jumlah_galon = max(1, $this->jumlah_galon + $delta);
    }

    public function kurangiJumlah(): void
    {
        $this->jumlah_galon = max(1, $this->jumlah_galon - 1);
    }

    public function setJumlah(int $jumlah): void
    {
        $this->jumlah_galon = max(1, $jumlah);
    }

    public function rupiah(int $angka): string
    {
        return 'Rp' . number_format($angka, 0, ',', '.');
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'jenis_item' => ['required', 'string', Rule::enum(JenisItem::class)],
            'metode_bayar' => ['required', 'string', Rule::enum(MetodePembayaran::class)],
            'jumlah_galon' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'jenis_item.required' => __('Pilih jenis item transaksi.'),
            'jenis_item.Illuminate\Validation\Rules\Enum' => __('Jenis item tidak sah.'),
            'metode_bayar.required' => __('Pilih metode pembayaran.'),
            'metode_bayar.Illuminate\Validation\Rules\Enum' => __('Metode pembayaran tidak sah.'),
            'jumlah_galon.required' => __('Jumlah galon wajib diisi.'),
            'jumlah_galon.integer' => __('Jumlah galon harus berupa bilangan bulat.'),
            'jumlah_galon.min' => __('Jumlah galon minimal 1.'),
            'jumlah_galon.max' => __('Jumlah galon maksimal 500 per transaksi walk-in.'),
        ];
    }

    public function simpan(): void
    {
        // 1. Otorisasi ulang peran Kasir pada setiap request simpan
        $kasir = $this->authorizeKasir();

        // 2. Kasir tanpa cabang atau cabang nonaktif tidak bisa menyimpan (pesan aman, bukan error 500)
        if ($kasir->cabang_id === null || !$kasir->cabang || !$kasir->cabang->is_active) {
            $this->addError('cabang', __('Cabang Anda sedang tidak aktif atau belum ditentukan. Transaksi walk-in tidak dapat disimpan.'));

            return;
        }

        // 3. Validasi input form
        $validated = $this->validate();

        // 4. Hitung total di SERVER murni dari konfigurasi; nilai dari browser diabaikan
        $itemEnum = JenisItem::from($validated['jenis_item']);
        $metodeEnum = MetodePembayaran::from($validated['metode_bayar']);
        $jumlah = (int) $validated['jumlah_galon'];

        $hargaSatuan = match ($itemEnum) {
            JenisItem::GalonBaru => (int) config('sigalon.harga.galon_baru'),
            JenisItem::IsiUlang => (int) config('sigalon.harga.walk_in'),
        };
        $totalHargaServer = $jumlah * $hargaSatuan;

        // 5. Simpan transaksi walk-in selesai dengan field otomatis dari server
        $transaksi = new Transaksi();
        $transaksi->cabang_id = (int) $kasir->cabang_id; // Dari server (auth Kasir), bukan input browser
        $transaksi->pelanggan_id = null; // Transaksi walk-in tanpa data pelanggan
        $transaksi->kurir_id = null;
        $transaksi->jenis_kanal = Kanal::WalkIn;
        $transaksi->jenis_item = $itemEnum;
        $transaksi->jumlah_galon = $jumlah;
        $transaksi->total_harga = $totalHargaServer; // Total dihitung di server
        $transaksi->metode_bayar = $metodeEnum;
        $transaksi->status = StatusTransaksi::Selesai;
        $transaksi->tanggal = CarbonImmutable::today();
        $transaksi->nama_pemesan = null;
        $transaksi->no_hp_pemesan = null;
        $transaksi->alamat_antar = null;
        $transaksi->dicatat_oleh = (int) $kasir->id; // Kasir yang login
        $transaksi->diverifikasi_oleh = null;
        $transaksi->save();

        // 6. Tampilkan konfirmasi berisi total setelah tersimpan
        $this->transaksiTerakhir = [
            'id' => (int) $transaksi->id,
            'item' => $transaksi->jenis_item->label(),
            'jumlah' => $transaksi->jumlah_galon,
            'harga_satuan' => $hargaSatuan,
            'total' => $totalHargaServer,
            'metode' => $transaksi->metode_bayar->label(),
            'tanggal' => $transaksi->tanggal->translatedFormat('d F Y'),
        ];

        // 7. Form kembali kosong / default untuk transaksi baru berikutnya
        $this->jenis_item = JenisItem::IsiUlang->value;
        $this->jumlah_galon = 1;
        $this->metode_bayar = MetodePembayaran::Tunai->value;
        $this->browser_total = null;
        $this->browser_cabang_id = null;
        $this->resetValidation();
    }
}; ?>

<div class="flex w-full flex-col gap-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-card border border-line/60 md:flex-row md:items-center md:justify-between"
        data-test="header-transaksi-kasir">
        <div class="flex flex-col gap-1">
            <span class="text-xs font-bold uppercase tracking-wider text-primary">
                {{ __('KASIR DEPOT · WALK-IN') }}
            </span>
            <h1 class="text-2xl font-bold tracking-tight text-deep">
                {{ __('Input Transaksi Walk-in & Galon Baru') }}
            </h1>
            <p class="text-xs text-muted">
                {{ __('Pencatatan transaksi langsung di depot untuk isi ulang air dan penjualan galon kosong baru.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 rounded-xl bg-surface-low px-3.5 py-2 text-xs font-semibold text-ink"
                data-test="info-cabang-kasir">
                <flux:icon.building-storefront variant="mini" class="size-4 text-primary" />
                <span>{{ $this->namaCabang }}</span>
                @if ($this->cabangAktif)
                    <span class="size-2 rounded-full bg-success"></span>
                @else
                    <span class="size-2 rounded-full bg-danger"></span>
                @endif
            </div>
        </div>
    </div>

    {{-- Banner Notifikasi Cabang Nonaktif --}}
    @if (!$this->cabangAktif)
        <div class="flex items-start gap-3 rounded-2xl border border-warning/40 bg-warning-soft/80 p-4 text-ink shadow-xs"
            data-test="pesan-cabang-nonaktif">
            <flux:icon.exclamation-triangle variant="mini" class="size-5 shrink-0 text-warning" />
            <div class="flex flex-col gap-0.5 text-xs">
                <span class="font-bold text-ink">{{ __('Perhatian: Cabang Tidak Aktif') }}</span>
                <span class="text-muted">
                    {{ __('Cabang tempat Anda bertugas saat ini berstatus nonaktif atau belum terhubung. Transaksi walk-in tidak dapat disimpan sampai cabang diaktifkan kembali.') }}
                </span>
            </div>
        </div>
    @endif

    {{-- Alert Error Cabang --}}
    @error('cabang')
        <div class="flex items-start gap-3 rounded-2xl border border-danger/40 bg-danger-soft/80 p-4 text-ink shadow-xs"
            data-test="error-cabang">
            <flux:icon.x-circle variant="mini" class="size-5 shrink-0 text-danger" />
            <div class="text-xs font-semibold text-danger">{{ $message }}</div>
        </div>
    @enderror

    {{-- Konfirmasi Transaksi Berhasil --}}
    @if ($transaksiTerakhir)
        <div class="flex flex-col gap-3 rounded-2xl border border-success/40 bg-success-soft/70 p-5 shadow-xs transition"
            data-test="konfirmasi-transaksi">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-success text-white shadow-xs">
                        <flux:icon.check variant="mini" class="size-5" />
                    </span>
                    <div>
                        <h4 class="text-sm font-bold text-ink">{{ __('Transaksi Berhasil Disimpan!') }}</h4>
                        <p class="text-xs text-muted">
                            {{ __('Data transaksi langsung berstatus selesai dan tercatat ke buku kas cabang.') }}</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('transaksiTerakhir', null)"
                    class="text-xs font-semibold text-muted hover:text-ink">
                    {{ __('Tutup') }} &times;
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-success/20 pt-3 text-xs">
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <span class="text-muted">{{ __('Item:') }}</span>
                        <span class="font-bold text-ink ml-1">{{ $transaksiTerakhir['item'] }}</span>
                    </div>
                    <div>
                        <span class="text-muted">{{ __('Jumlah:') }}</span>
                        <span class="font-bold text-ink ml-1">{{ $transaksiTerakhir['jumlah'] }} galon</span>
                    </div>
                    <div>
                        <span class="text-muted">{{ __('Metode:') }}</span>
                        <span class="font-bold text-ink ml-1">{{ $transaksiTerakhir['metode'] }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-muted">{{ __('Total Dibayar:') }}</span>
                    <span class="text-lg font-bold text-success" data-test="konfirmasi-total">
                        {{ $this->rupiah($transaksiTerakhir['total']) }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- Grid Form Transaksi (2 Kolom) --}}
    <form wire:submit="simpan" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Kolom Kiri: Input Item, Jumlah, dan Metode Bayar (8 Kolom) --}}
        <div class="flex flex-col gap-6 lg:col-span-7 xl:col-span-8">
            {{-- Bagian 1: Pilih Jenis Item --}}
            <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card border border-line/60">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-7 items-center justify-center rounded-lg bg-primary-soft text-primary text-xs font-bold">1</span>
                        <h3 class="text-sm font-bold text-deep">{{ __('Pilih Jenis Item') }}</h3>
                    </div>
                    <span class="text-[11px] text-muted">{{ __('Satu transaksi untuk satu jenis item') }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Opsi A: Air Isi Ulang --}}
                    <button type="button" wire:click="pilihItem('isi_ulang')" data-test="opsi-isi-ulang"
                        class="flex flex-col gap-2 rounded-xl border p-4 text-left transition relative cursor-pointer {{ $jenis_item === 'isi_ulang' ? 'border-primary bg-primary-soft/30 ring-2 ring-primary/40 shadow-xs' : 'border-line bg-white hover:border-line/80 hover:bg-surface/50' }}">
                        <div class="flex items-center justify-between">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-2.5 py-0.5 text-[11px] font-semibold text-primary">
                                <flux:icon.arrow-path variant="mini" class="size-3" />
                                {{ __('Isi Ulang') }}
                            </span>
                            @if ($jenis_item === 'isi_ulang')
                                <flux:icon.check-circle variant="mini" class="size-5 text-primary" />
                            @endif
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-sm font-bold text-ink">{{ __('Air Isi Ulang') }}</span>
                            <span class="text-xs text-muted">{{ __('Pelanggan membawa galon sendiri') }}</span>
                        </div>

                        <div class="mt-2 pt-2 border-t border-line/60 flex items-baseline justify-between">
                            <span class="text-[11px] text-muted">{{ __('Tarif resmi:') }}</span>
                            <span class="text-base font-bold text-primary" data-test="harga-satuan-isi-ulang">
                                {{ $this->rupiah((int) config('sigalon.harga.walk_in')) }}
                            </span>
                        </div>
                    </button>

                    {{-- Opsi B: Galon Kosong Baru --}}
                    <button type="button" wire:click="pilihItem('galon_baru')" data-test="opsi-galon-baru"
                        class="flex flex-col gap-2 rounded-xl border p-4 text-left transition relative cursor-pointer {{ $jenis_item === 'galon_baru' ? 'border-primary bg-primary-soft/30 ring-2 ring-primary/40 shadow-xs' : 'border-line bg-white hover:border-line/80 hover:bg-surface/50' }}">
                        <div class="flex items-center justify-between">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-warning-soft px-2.5 py-0.5 text-[11px] font-semibold text-warning">
                                <flux:icon.sparkles variant="mini" class="size-3" />
                                {{ __('Galon Baru') }}
                            </span>
                            @if ($jenis_item === 'galon_baru')
                                <flux:icon.check-circle variant="mini" class="size-5 text-primary" />
                            @endif
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-sm font-bold text-ink">{{ __('Galon Kosong Baru') }}</span>
                            <span class="text-xs text-muted">{{ __('Khusus walk-in (nilai sementara)') }}</span>
                        </div>

                        <div class="mt-2 pt-2 border-t border-line/60 flex items-baseline justify-between">
                            <span class="text-[11px] text-muted">{{ __('Tarif sementara:') }}</span>
                            <span class="text-base font-bold text-primary" data-test="harga-satuan-galon-baru">
                                {{ $this->rupiah((int) config('sigalon.harga.galon_baru')) }}
                            </span>
                        </div>
                    </button>
                </div>

                @error('jenis_item')
                    <span class="text-xs font-semibold text-danger" data-test="error-jenis-item">{{ $message }}</span>
                @enderror
            </div>

            {{-- Bagian 2: Jumlah Galon --}}
            <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card border border-line/60">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-7 items-center justify-center rounded-lg bg-primary-soft text-primary text-xs font-bold">2</span>
                        <h3 class="text-sm font-bold text-deep">{{ __('Jumlah Galon') }}</h3>
                    </div>
                    <span class="text-[11px] text-muted">{{ __('Minimal 1 galon') }}</span>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    {{-- Stepper Kontrol (- Angka +) --}}
                    <div class="flex items-center rounded-xl border border-line bg-surface p-1 shadow-xs">
                        <button type="button" wire:click="kurangiJumlah" data-test="btn-kurang-jumlah"
                            class="flex size-10 items-center justify-center rounded-lg bg-white text-ink hover:bg-surface-low active:bg-primary-soft transition disabled:opacity-40 shadow-2xs"
                            @if ($jumlah_galon <= 1) disabled @endif>
                            <flux:icon.minus variant="mini" class="size-4" />
                        </button>

                        <input type="number" min="1" max="500" wire:model.live="jumlah_galon"
                            data-test="input-jumlah-galon"
                            class="w-20 border-0 bg-transparent text-center text-lg font-bold text-ink focus:outline-none focus:ring-0" />

                        <button type="button" wire:click="tambahJumlah(1)" data-test="btn-tambah-jumlah"
                            class="flex size-10 items-center justify-center rounded-lg bg-white text-ink hover:bg-surface-low active:bg-primary-soft transition shadow-2xs">
                            <flux:icon.plus variant="mini" class="size-4" />
                        </button>
                    </div>

                    {{-- Tombol Pintasan Cepat (+1, +2, +5, +10) --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="setJumlah(1)"
                            class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink hover:border-primary hover:text-primary transition shadow-2xs">
                            {{ __('1') }}
                        </button>
                        <button type="button" wire:click="setJumlah(2)"
                            class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink hover:border-primary hover:text-primary transition shadow-2xs">
                            {{ __('2') }}
                        </button>
                        <button type="button" wire:click="setJumlah(5)"
                            class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink hover:border-primary hover:text-primary transition shadow-2xs">
                            {{ __('5') }}
                        </button>
                        <button type="button" wire:click="setJumlah(10)"
                            class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-ink hover:border-primary hover:text-primary transition shadow-2xs">
                            {{ __('10') }}
                        </button>
                        <button type="button" wire:click="tambahJumlah(5)"
                            class="rounded-lg border border-primary/30 bg-primary-soft/60 px-3 py-1.5 text-xs font-bold text-primary hover:bg-primary-soft transition shadow-2xs">
                            {{ __('+5') }}
                        </button>
                    </div>
                </div>

                @error('jumlah_galon')
                    <span class="text-xs font-semibold text-danger" data-test="error-jumlah-galon">{{ $message }}</span>
                @enderror
            </div>

            {{-- Bagian 3: Metode Pembayaran --}}
            <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card border border-line/60">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-7 items-center justify-center rounded-lg bg-primary-soft text-primary text-xs font-bold">3</span>
                        <h3 class="text-sm font-bold text-deep">{{ __('Metode Pembayaran') }}</h3>
                    </div>
                    <span class="text-[11px] text-muted">{{ __('Pilih Tunai atau QR statis') }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Metode A: Tunai --}}
                    <button type="button" wire:click="pilihMetode('tunai')" data-test="metode-tunai"
                        class="flex items-center gap-3 rounded-xl border p-4 text-left transition cursor-pointer {{ $metode_bayar === 'tunai' ? 'border-primary bg-primary text-white shadow-xs' : 'border-line bg-surface hover:bg-surface-low text-ink' }}">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $metode_bayar === 'tunai' ? 'bg-white/20 text-white' : 'bg-white text-primary border border-line' }}">
                            <flux:icon.banknotes variant="mini" class="size-5" />
                        </span>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold">{{ __('Tunai') }}</span>
                            <span class="text-[11px] {{ $metode_bayar === 'tunai' ? 'text-white/80' : 'text-muted' }}">
                                {{ __('Uang tunai diterima kasir') }}
                            </span>
                        </div>
                    </button>

                    {{-- Metode B: QR Statis --}}
                    <button type="button" wire:click="pilihMetode('qr')" data-test="metode-qr"
                        class="flex items-center gap-3 rounded-xl border p-4 text-left transition cursor-pointer {{ $metode_bayar === 'qr' ? 'border-primary bg-primary text-white shadow-xs' : 'border-line bg-surface hover:bg-surface-low text-ink' }}">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $metode_bayar === 'qr' ? 'bg-white/20 text-white' : 'bg-white text-primary border border-line' }}">
                            <flux:icon.qr-code variant="mini" class="size-5" />
                        </span>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold">{{ __('QR Statis') }}</span>
                            <span class="text-[11px] {{ $metode_bayar === 'qr' ? 'text-white/80' : 'text-muted' }}">
                                {{ __('Scan QR depot (dicatat saja)') }}
                            </span>
                        </div>
                    </button>
                </div>

                @error('metode_bayar')
                    <span class="text-xs font-semibold text-danger" data-test="error-metode-bayar">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Kolom Kanan: Ringkasan & Konfirmasi Pembayaran (4-5 Kolom) --}}
        <div class="flex flex-col gap-6 lg:col-span-5 xl:col-span-4">
            <div class="flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-card border border-line/60 sticky top-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-muted">
                        {{ __('RINGKASAN TRANSAKSI') }}
                    </span>
                    <h3 class="text-base font-bold text-deep">
                        {{ __('Rincian Pembayaran') }}
                    </h3>
                </div>

                {{-- Panel Total Harga Besar --}}
                <div class="flex flex-col gap-1 rounded-xl bg-surface-low p-4 text-center">
                    <span class="text-xs font-medium text-muted">{{ __('Total yang harus dibayar') }}</span>
                    <span class="text-3xl font-extrabold text-primary" data-test="total-harga">
                        {{ $this->rupiah($this->totalHarga) }}
                    </span>
                    <span class="text-[11px] text-muted">
                        {{ $this->jumlah_galon }} × {{ $this->rupiah($this->hargaSatuan) }}
                    </span>
                </div>

                {{-- Tabel Rincian Data --}}
                <div class="flex flex-col divide-y divide-line/60 text-xs">
                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-muted">{{ __('Jenis Item') }}</span>
                        <span class="font-semibold text-ink" data-test="ringkasan-item">
                            {{ $jenis_item === 'galon_baru' ? __('Galon Kosong Baru') : __('Air Isi Ulang') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-muted">{{ __('Jumlah') }}</span>
                        <span class="font-semibold text-ink" data-test="ringkasan-jumlah">
                            {{ $jumlah_galon }} {{ __('galon') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-muted">{{ __('Metode Bayar') }}</span>
                        <span class="font-semibold text-ink" data-test="ringkasan-metode">
                            {{ $metode_bayar === 'qr' ? __('QR Statis') : __('Tunai') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-muted">{{ __('Kanal') }}</span>
                        <span
                            class="inline-flex items-center gap-1 rounded-md bg-surface px-2 py-0.5 font-medium text-ink">
                            {{ __('Walk-in') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-muted">{{ __('Status Akhir') }}</span>
                        <span
                            class="inline-flex items-center gap-1 rounded-md bg-success-soft px-2 py-0.5 font-semibold text-success">
                            {{ __('Selesai') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-muted">{{ __('Cabang') }}</span>
                        <span class="font-medium text-ink truncate max-w-[150px]">{{ $this->namaCabang }}</span>
                    </div>

                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-muted">{{ __('Dicatat Oleh') }}</span>
                        <span class="font-medium text-ink truncate max-w-[150px]">{{ auth()->user()->name }}</span>
                    </div>
                </div>

                {{-- Tombol Aksi Simpan --}}
                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" wire:loading.attr="disabled" data-test="btn-simpan-transaksi"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3 px-4 text-sm font-bold text-white shadow-xs hover:bg-primary-strong active:scale-[0.99] transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        @if (!$this->cabangAktif) disabled @endif>
                        <span wire:loading.remove wire:target="simpan" class="flex items-center gap-2">
                            <flux:icon.check-circle variant="mini" class="size-4" />
                            {{ __('Simpan Transaksi') }}
                        </span>

                        <span wire:loading wire:target="simpan" class="flex items-center gap-2">
                            <svg class="size-4 animate-spin text-white" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                            {{ __('Menyimpan...') }}
                        </span>
                    </button>

                    <span class="text-center text-[11px] text-muted">
                        {{ __('Total dihitung otomatis oleh server dari konfigurasi harga resmi.') }}
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>