<?php

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\StatusPesananService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Input Pesanan COD Manual')]
class extends Component {
    /**
     * Nama pemesan (pelanggan).
     */
    public string $nama_pemesan = '';

    /**
     * Nomor telepon / WhatsApp pemesan.
     */
    public string $no_hp_pemesan = '';

    /**
     * Alamat lengkap pengantaran galon.
     */
    public string $alamat_antar = '';

    /**
     * Jumlah galon yang dipesan (bilangan bulat, minimal 1).
     */
    public int $jumlah_galon = 1;

    /**
     * Metode pembayaran: 'tunai' atau 'qr'.
     */
    public string $metode_bayar = 'tunai';

    /**
     * Field pengaman: mengabaikan manipulasi browser/klien.
     */
    public ?int $browser_total = null;
    public ?int $browser_cabang_id = null;

    /**
     * Data konfirmasi pesanan yang baru saja berhasil disimpan.
     *
     * @var array{id: int, nama_pemesan: string, no_hp_pemesan: string, alamat_antar: string, jumlah: int, harga_satuan: int, total: int, metode: string, status: string, tanggal: string}|null
     */
    public ?array $pesananTerakhir = null;

    public function mount(): void
    {
        $this->authorizeKasir();
    }

    public function hydrate(): void
    {
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

        if (! $user || ! $user->cabang_id) {
            return false;
        }

        return (bool) Cabang::query()
            ->where('id', $user->cabang_id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Harga satuan air galon COD dari file konfigurasi (PRD §6: Rp7.000 / galon).
     */
    #[Computed]
    public function hargaSatuan(): int
    {
        return (int) config('sigalon.harga.cod', 7000);
    }

    /**
     * Total harga dinamis yang dihitung di server.
     */
    #[Computed]
    public function totalHarga(): int
    {
        $jumlah = max(0, (int) $this->jumlah_galon);

        return $jumlah * $this->hargaSatuan;
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

    public function inputPesananBaru(): void
    {
        $this->pesananTerakhir = null;
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
            'nama_pemesan' => ['required', 'string', 'min:2', 'max:100'],
            'no_hp_pemesan' => ['required', 'string', 'regex:/^[0-9+\-\s]{9,20}$/'],
            'alamat_antar' => ['required', 'string', 'min:5', 'max:500'],
            'jumlah_galon' => ['required', 'integer', 'min:1', 'max:500'],
            'metode_bayar' => ['required', 'string', Rule::enum(MetodePembayaran::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'nama_pemesan.required' => __('Nama pemesan wajib diisi.'),
            'nama_pemesan.min' => __('Nama pemesan minimal 2 karakter.'),
            'nama_pemesan.max' => __('Nama pemesan maksimal 100 karakter.'),
            'no_hp_pemesan.required' => __('Nomor telepon / WhatsApp pemesan wajib diisi.'),
            'no_hp_pemesan.regex' => __('Format nomor telepon tidak sah (gunakan 9-20 digit angka).'),
            'alamat_antar.required' => __('Alamat pengantaran wajib diisi.'),
            'alamat_antar.min' => __('Alamat pengantaran minimal 5 karakter.'),
            'alamat_antar.max' => __('Alamat pengantaran maksimal 500 karakter.'),
            'jumlah_galon.required' => __('Jumlah galon wajib diisi.'),
            'jumlah_galon.integer' => __('Jumlah galon harus berupa bilangan bulat.'),
            'jumlah_galon.min' => __('Jumlah galon minimal 1.'),
            'jumlah_galon.max' => __('Jumlah galon maksimal 500 per pesanan.'),
            'metode_bayar.required' => __('Pilih metode pembayaran.'),
            'metode_bayar.Illuminate\Validation\Rules\Enum' => __('Metode pembayaran tidak sah.'),
        ];
    }

    public function simpan(): void
    {
        // 1. Otorisasi ulang peran Kasir pada setiap request simpan
        $kasir = $this->authorizeKasir();

        // 2. Kasir tanpa cabang atau cabang nonaktif tidak bisa menyimpan pesanan
        if ($kasir->cabang_id === null || ! $kasir->cabang || ! $kasir->cabang->is_active) {
            $this->addError('cabang', __('Cabang Anda sedang tidak aktif atau belum ditentukan. Pesanan COD tidak dapat disimpan.'));

            return;
        }

        // 3. Validasi input form
        $validated = $this->validate();

        // 4. Hitung total di SERVER murni dari konfigurasi; nilai browser diabaikan
        $jumlah = (int) $validated['jumlah_galon'];
        $hargaSatuan = $this->hargaSatuan;
        $totalHargaServer = $jumlah * $hargaSatuan;
        $metodeEnum = MetodePembayaran::from($validated['metode_bayar']);

        // 5. Simpan pesanan COD dan catat status awal dalam satu transaksi database
        $pesanan = DB::transaction(function () use ($kasir, $validated, $totalHargaServer, $metodeEnum, $jumlah): Transaksi {
            $transaksi = new Transaksi();
            $transaksi->cabang_id = (int) $kasir->cabang_id;
            $transaksi->pelanggan_id = null; // COD manual tanpa akun pelanggan (PRD §7.D, ERD v1.1)
            $transaksi->kurir_id = null; // Kurir akan ditugaskan di US-12
            $transaksi->jenis_kanal = Kanal::Cod;
            $transaksi->jenis_item = JenisItem::IsiUlang; // Layanan COD hanya air isi ulang
            $transaksi->jumlah_galon = $jumlah;
            $transaksi->total_harga = $totalHargaServer;
            $transaksi->metode_bayar = $metodeEnum;
            $transaksi->status = StatusTransaksi::Diproses;
            $transaksi->tanggal = CarbonImmutable::today();
            $transaksi->nama_pemesan = trim($validated['nama_pemesan']);
            $transaksi->no_hp_pemesan = trim($validated['no_hp_pemesan']);
            $transaksi->alamat_antar = trim($validated['alamat_antar']);
            $transaksi->dicatat_oleh = (int) $kasir->id;
            $transaksi->diverifikasi_oleh = null;
            $transaksi->save();

            // Panggil StatusPesananService untuk mencatat status awal di riwayat_status (US-10/US-12)
            app(StatusPesananService::class)->catatStatusAwal($transaksi, $kasir);

            return $transaksi;
        });

        // 6. Siapkan data konfirmasi pesanan
        $this->pesananTerakhir = [
            'id' => (int) $pesanan->id,
            'nama_pemesan' => (string) $pesanan->nama_pemesan,
            'no_hp_pemesan' => (string) $pesanan->no_hp_pemesan,
            'alamat_antar' => (string) $pesanan->alamat_antar,
            'jumlah' => $pesanan->jumlah_galon,
            'harga_satuan' => $hargaSatuan,
            'total' => $totalHargaServer,
            'metode' => $pesanan->metode_bayar->label(),
            'status' => $pesanan->status->label(),
            'tanggal' => CarbonImmutable::today()->translatedFormat('d F Y'),
        ];

        // 7. Reset form untuk input berikutnya
        $this->nama_pemesan = '';
        $this->no_hp_pemesan = '';
        $this->alamat_antar = '';
        $this->jumlah_galon = 1;
        $this->metode_bayar = 'tunai';
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col gap-2 rounded-2xl bg-white p-6 shadow-card border border-line/60">
        <span class="text-xs font-bold uppercase tracking-wider text-primary">{{ __('LOKET KASIR · KANAL COD') }}</span>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1" class="text-deep! font-bold text-2xl tracking-tight">
                    {{ __('Input Pesanan COD Manual') }}
                </flux:heading>
                <flux:text class="text-muted text-sm mt-1">
                    {{ __('Catat pesanan antar-jemput air galon dari telepon atau WhatsApp pelanggan.') }}
                </flux:text>
            </div>

            <div class="inline-flex items-center gap-2 rounded-xl bg-surface-low px-3.5 py-2 text-xs font-semibold text-primary self-start md:self-auto">
                <flux:icon.building-storefront variant="mini" class="size-4 shrink-0" />
                <span>{{ auth()->user()?->cabang?->nama ?? __('Cabang Belum Ditentukan') }}</span>
            </div>
        </div>
    </div>

    {{-- Alert Cabang Tidak Aktif --}}
    @if (! $this->cabangAktif)
        <div class="flex items-start gap-3 rounded-2xl bg-danger-soft p-5 border border-danger/20 text-danger" data-test="alert-cabang-nonaktif">
            <flux:icon.exclamation-triangle variant="mini" class="size-5 shrink-0 mt-0.5" />
            <div class="text-sm">
                <span class="font-bold block">{{ __('Peringatan: Cabang Tidak Aktif') }}</span>
                <span>{{ __('Akun Anda belum terikat ke cabang aktif. Formulir tidak dapat disimpan sampai cabang diaktifkan kembali oleh Super Admin.') }}</span>
            </div>
        </div>
    @endif

    @error('cabang')
        <div class="flex items-start gap-3 rounded-2xl bg-danger-soft p-4 border border-danger/20 text-danger text-sm" data-test="error-cabang">
            <flux:icon.exclamation-circle variant="mini" class="size-5 shrink-0" />
            <span>{{ $message }}</span>
        </div>
    @enderror

    {{-- Kartu Sukses / Konfirmasi Pesanan --}}
    @if ($pesananTerakhir !== null)
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card border-2 border-primary/30" data-test="konfirmasi-sukses">
            <div class="flex items-start justify-between gap-3 border-b border-line pb-4">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary">
                        <flux:icon.check-circle variant="mini" class="size-6" />
                    </span>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-primary">{{ __('PESANAN COD BERHASIL DIBUAT') }}</span>
                        <div class="text-lg font-bold text-ink" data-test="id-pesanan">
                            {{ __('Pesanan #COD-:id', ['id' => $pesananTerakhir['id']]) }}
                        </div>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold text-primary" data-test="status-pesanan">
                    <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                    {{ $pesananTerakhir['status'] }}
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-xs">
                <div class="rounded-xl bg-surface-low/60 p-3.5 border border-line">
                    <span class="text-muted block text-[11px] uppercase font-semibold">{{ __('Pemesan') }}</span>
                    <span class="font-bold text-ink text-sm block mt-0.5" data-test="nama-pemesan-sukses">{{ $pesananTerakhir['nama_pemesan'] }}</span>
                    <span class="text-muted block mt-0.5" data-test="no-hp-pemesan-sukses">{{ $pesananTerakhir['no_hp_pemesan'] }}</span>
                </div>

                <div class="rounded-xl bg-surface-low/60 p-3.5 border border-line">
                    <span class="text-muted block text-[11px] uppercase font-semibold">{{ __('Alamat Antar') }}</span>
                    <span class="font-semibold text-ink text-xs block mt-0.5 line-clamp-2" data-test="alamat-antar-sukses">{{ $pesananTerakhir['alamat_antar'] }}</span>
                </div>

                <div class="rounded-xl bg-surface-low/60 p-3.5 border border-line">
                    <span class="text-muted block text-[11px] uppercase font-semibold">{{ __('Layanan & Jumlah') }}</span>
                    <span class="font-bold text-ink text-sm block mt-0.5" data-test="jumlah-galon-sukses">{{ __(':count Galon Isi Ulang', ['count' => $pesananTerakhir['jumlah']]) }}</span>
                    <span class="text-muted block mt-0.5">{{ __('Metode: :metode', ['metode' => $pesananTerakhir['metode']]) }}</span>
                </div>

                <div class="rounded-xl bg-surface-low/60 p-3.5 border border-line">
                    <span class="text-muted block text-[11px] uppercase font-semibold">{{ __('Total Tagihan') }}</span>
                    <span class="font-bold text-primary text-base block mt-0.5" data-test="total-harga-sukses">{{ $this->rupiah($pesananTerakhir['total']) }}</span>
                    <span class="text-muted block mt-0.5">{{ __('COD: dibayar saat diantar') }}</span>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <flux:button wire:click="inputPesananBaru" variant="primary" size="sm" icon="plus" data-test="tombol-input-lagi">
                    {{ __('Input Pesanan COD Baru') }}
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Form Input COD Manual --}}
    <form wire:submit.prevent="simpan" class="grid grid-cols-1 gap-6 lg:grid-cols-12" data-test="form-pesanan-cod">
        {{-- Kolom Kiri: Data Pemesan & Pengantaran --}}
        <div class="flex flex-col gap-6 lg:col-span-7">
            <div class="flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-card border border-line/60">
                <div class="flex items-center gap-2.5 border-b border-line pb-3.5">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-primary-soft text-primary">
                        <flux:icon.user variant="mini" class="size-4" />
                    </span>
                    <flux:heading level="2" class="text-deep! font-bold text-base">
                        {{ __('Data Pemesan & Alamat Antar') }}
                    </flux:heading>
                </div>

                <div class="grid gap-4">
                    {{-- Nama Pemesan --}}
                    <div>
                        <flux:label for="nama_pemesan" class="text-xs font-semibold text-ink">
                            {{ __('Nama Pemesan') }} <span class="text-danger">*</span>
                        </flux:label>
                        <flux:input
                            id="nama_pemesan"
                            wire:model="nama_pemesan"
                            type="text"
                            placeholder="{{ __('Contoh: Ibu Siti / Pak Budi') }}"
                            class="mt-1"
                            data-test="input-nama-pemesan"
                        />
                        @error('nama_pemesan')
                            <span class="text-danger text-xs mt-1 block" data-test="error-nama-pemesan">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Nomor HP / WhatsApp --}}
                    <div>
                        <flux:label for="no_hp_pemesan" class="text-xs font-semibold text-ink">
                            {{ __('Nomor Telepon / WhatsApp') }} <span class="text-danger">*</span>
                        </flux:label>
                        <flux:input
                            id="no_hp_pemesan"
                            wire:model="no_hp_pemesan"
                            type="tel"
                            placeholder="{{ __('Contoh: 081234567890') }}"
                            class="mt-1"
                            data-test="input-no-hp-pemesan"
                        />
                        @error('no_hp_pemesan')
                            <span class="text-danger text-xs mt-1 block" data-test="error-no-hp-pemesan">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Alamat Antar --}}
                    <div>
                        <flux:label for="alamat_antar" class="text-xs font-semibold text-ink">
                            {{ __('Alamat Lengkap Pengantaran') }} <span class="text-danger">*</span>
                        </flux:label>
                        <flux:textarea
                            id="alamat_antar"
                            wire:model="alamat_antar"
                            rows="3"
                            placeholder="{{ __('Contoh: Jl. Merpati No. 12, RT 02/RW 04, Kel. Dadok (Depan Musholla)') }}"
                            class="mt-1"
                            data-test="input-alamat-antar"
                        />
                        @error('alamat_antar')
                            <span class="text-danger text-xs mt-1 block" data-test="error-alamat-antar">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Rincian Pesanan, Metode Bayar, & Ringkasan --}}
        <div class="flex flex-col gap-6 lg:col-span-5">
            {{-- Card Jumlah Galon & Metode Bayar --}}
            <div class="flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-card border border-line/60">
                <div class="flex items-center gap-2.5 border-b border-line pb-3.5">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-primary-soft text-primary">
                        <flux:icon.truck variant="mini" class="size-4" />
                    </span>
                    <flux:heading level="2" class="text-deep! font-bold text-base">
                        {{ __('Layanan & Kuantitas') }}
                    </flux:heading>
                </div>

                {{-- Info Layanan COD --}}
                <div class="rounded-xl border border-primary/20 bg-primary-soft/20 p-3.5 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-ink block">{{ __('Isi Ulang Air Galon (COD)') }}</span>
                        <span class="text-[11px] text-muted">{{ __('Antar-jemput ke lokasi pelanggan') }}</span>
                    </div>
                    <span class="text-xs font-bold text-primary">{{ $this->rupiah($this->hargaSatuan) }} <span class="text-[10px] text-muted font-normal">/ gln</span></span>
                </div>

                {{-- Stepper Kuantitas Galon --}}
                <div>
                    <flux:label class="text-xs font-semibold text-ink">
                        {{ __('Jumlah Galon') }} <span class="text-danger">*</span>
                    </flux:label>
                    <div class="mt-1.5 flex items-center justify-between rounded-xl border border-line bg-surface p-1.5">
                        <button
                            type="button"
                            wire:click="kurangiJumlah"
                            class="flex size-9 items-center justify-center rounded-lg bg-white border border-line font-bold text-ink hover:bg-surface-low transition cursor-pointer"
                            aria-label="Kurangi galon"
                            data-test="tombol-kurang"
                        >
                            −
                        </button>
                        <div class="flex flex-col items-center">
                            <span class="text-xl font-bold text-ink leading-tight" data-test="jumlah-galon-text">
                                {{ $this->jumlah_galon }}
                            </span>
                            <span class="text-[10px] text-muted font-medium uppercase">{{ __('Galon') }}</span>
                        </div>
                        <button
                            type="button"
                            wire:click="tambahJumlah(1)"
                            class="flex size-9 items-center justify-center rounded-lg bg-white border border-line font-bold text-ink hover:bg-surface-low transition cursor-pointer"
                            aria-label="Tambah galon"
                            data-test="tombol-tambah"
                        >
                            +
                        </button>
                    </div>

                    {{-- Tombol Cepat --}}
                    <div class="mt-2 flex items-center gap-1.5">
                        <span class="text-[11px] text-muted mr-1">{{ __('Pintasan:') }}</span>
                        <button
                            type="button"
                            wire:click="setJumlah(1)"
                            class="rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-semibold text-ink hover:bg-surface-low transition cursor-pointer"
                        >
                            1
                        </button>
                        <button
                            type="button"
                            wire:click="setJumlah(2)"
                            class="rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-semibold text-ink hover:bg-surface-low transition"
                        >
                            2
                        </button>
                        <button
                            type="button"
                            wire:click="setJumlah(3)"
                            class="rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-semibold text-ink hover:bg-surface-low transition"
                        >
                            3
                        </button>
                        <button
                            type="button"
                            wire:click="setJumlah(5)"
                            class="rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-semibold text-ink hover:bg-surface-low transition"
                        >
                            5
                        </button>
                        <button
                            type="button"
                            wire:click="tambahJumlah(5)"
                            class="rounded-lg border border-line bg-white px-2.5 py-1 text-xs font-semibold text-primary hover:bg-surface-low transition"
                        >
                            +5
                        </button>
                    </div>

                    @error('jumlah_galon')
                        <span class="text-danger text-xs mt-1 block" data-test="error-jumlah-galon">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Metode Pembayaran --}}
                <div>
                    <flux:label class="text-xs font-semibold text-ink">
                        {{ __('Metode Pembayaran (Ditagih Kurir)') }} <span class="text-danger">*</span>
                    </flux:label>
                    <div class="mt-1.5 grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            wire:click="pilihMetode('tunai')"
                            class="flex items-center justify-center gap-2 rounded-xl py-2.5 px-3 text-xs font-semibold transition cursor-pointer {{ $metode_bayar === 'tunai' ? 'bg-primary-strong text-white shadow-xs' : 'border border-line bg-surface text-ink hover:bg-surface-low' }}"
                            data-test="pilihan-tunai"
                        >
                            <flux:icon.banknotes variant="mini" class="size-4" />
                            <span>{{ __('Tunai (COD)') }}</span>
                        </button>

                        <button
                            type="button"
                            wire:click="pilihMetode('qr')"
                            class="flex items-center justify-center gap-2 rounded-xl py-2.5 px-3 text-xs font-semibold transition cursor-pointer {{ $metode_bayar === 'qr' ? 'bg-primary-strong text-white shadow-xs' : 'border border-line bg-surface text-ink hover:bg-surface-low' }}"
                            data-test="pilihan-qr"
                        >
                            <flux:icon.qr-code variant="mini" class="size-4" />
                            <span>{{ __('QR Statis') }}</span>
                        </button>
                    </div>
                    @error('metode_bayar')
                        <span class="text-danger text-xs mt-1 block" data-test="error-metode-bayar">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Kotak Total Tagihan --}}
                <div class="mt-2 rounded-xl bg-surface-low p-4 border border-primary/20 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-muted uppercase block">{{ __('Total Tagihan') }}</span>
                        <span class="text-[11px] text-muted">{{ __(':count galon × :harga', ['count' => $this->jumlah_galon, 'harga' => $this->rupiah($this->hargaSatuan)]) }}</span>
                    </div>
                    <span class="text-2xl font-bold text-primary" data-test="preview-total-harga">
                        {{ $this->rupiah($this->totalHarga) }}
                    </span>
                </div>

                {{-- Tombol Simpan Pesanan --}}
                <flux:button
                    type="submit"
                    variant="primary"
                    size="base"
                    class="w-full mt-2"
                    icon="truck"
                    wire:loading.attr="disabled"
                    data-test="tombol-simpan"
                >
                    <span wire:loading.remove wire:target="simpan">
                        {{ __('Simpan Pesanan COD') }}
                    </span>
                    <span wire:loading wire:target="simpan" class="inline-flex items-center gap-2">
                        <flux:icon.arrow-path variant="mini" class="animate-spin size-4" />
                        {{ __('Menyimpan Pesanan…') }}
                    </span>
                </flux:button>
            </div>
        </div>
    </form>
</section>

