<?php

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\StatusTransaksi;
use App\Models\Cabang;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Services\CustomerPhoneService;
use App\Services\QrCodeService;
use App\Services\StatusPesananService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pesan Air Galon')]
#[Layout('layouts.pelanggan')]
class extends Component {
    /**
     * Cabang tujuan pengantaran yang dipilih pelanggan.
     */
    public ?int $cabang_id = null;

    /**
     * Jumlah galon yang dipesan (bilangan bulat, minimal 1).
     */
    public int $jumlah_galon = 1;

    /**
     * Alamat pengantaran lengkap.
     */
    public string $alamat_antar = '';

    /**
     * Catatan tambahan dari pelanggan (opsional).
     */
    public string $catatan = '';

    /**
     * Metode pembayaran: 'tunai' atau 'qr'.
     */
    public string $metode_bayar = 'tunai';

    /**
     * Nomor HP untuk pelanggan Google yang belum melengkapi profil.
     */
    public string $phone = '';

    /**
     * Field pengaman: mengabaikan total harga yang dikirim dari browser.
     */
    public ?int $browser_total = null;

    /**
     * Data konfirmasi pesanan yang baru berhasil disimpan.
     *
     * @var array{id: int, nama_pemesan: string, alamat_antar: string, cabang: string, jumlah: int, harga_satuan: int, total: int, metode: string, metode_kode: string, status: string, tanggal: string}|null
     */
    public ?array $pesananBerhasil = null;

    public function mount(): void
    {
        $pelanggan = $this->authorizePelanggan();

        // Pre-fill alamat dari profil pelanggan jika sudah ada
        if ($pelanggan->alamat !== null && $pelanggan->alamat !== '') {
            $this->alamat_antar = $pelanggan->alamat;
        }
    }

    public function hydrate(): void
    {
        $this->authorizePelanggan();
    }

    /**
     * Memastikan hanya pelanggan yang terautentikasi (guard 'pelanggan') yang bisa mengakses.
     * Tamu dan staf (guard 'web') ditolak 403.
     */
    private function authorizePelanggan(): Pelanggan
    {
        /** @var Pelanggan|null $pelanggan */
        $pelanggan = auth('pelanggan')->user();

        abort_unless($pelanggan instanceof Pelanggan, 403);

        return $pelanggan;
    }

    /**
     * Daftar cabang yang aktif, diurutkan berdasarkan nama.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Cabang>
     */
    #[Computed]
    public function daftarCabang(): \Illuminate\Database\Eloquent\Collection
    {
        return Cabang::query()->where('is_active', true)->orderBy('nama')->get();
    }

    /**
     * Apakah pelanggan perlu melengkapi nomor HP (akun Google tanpa no_hp).
     */
    #[Computed]
    public function needsPhone(): bool
    {
        $pelanggan = auth('pelanggan')->user();

        return $pelanggan instanceof Pelanggan && ($pelanggan->no_hp === null || $pelanggan->no_hp === '');
    }

    /**
     * Harga satuan air galon COD dari konfigurasi (PRD §6: Rp7.000 / galon).
     */
    #[Computed]
    public function hargaSatuan(): int
    {
        return (int) config('sigalon.harga.cod', 7000);
    }

    /**
     * Total harga dihitung di server; nilai browser diabaikan.
     */
    #[Computed]
    public function totalHarga(): int
    {
        return max(0, $this->jumlah_galon) * $this->hargaSatuan;
    }

    /**
     * URL gambar QR statis dari storage disk public jika tersedia.
     */
    #[Computed]
    public function qrCodeUrl(): ?string
    {
        return app(QrCodeService::class)->getQrCodeUrl();
    }

    public function pilihMetode(string $metode): void
    {
        if (in_array($metode, [MetodePembayaran::Tunai->value, MetodePembayaran::Qr->value], true)) {
            $this->metode_bayar = $metode;
        }
    }

    public function tambahJumlah(): void
    {
        $this->jumlah_galon = min(20, $this->jumlah_galon + 1);
    }

    public function kurangiJumlah(): void
    {
        $this->jumlah_galon = max(1, $this->jumlah_galon - 1);
    }

    public function setJumlah(int $jumlah): void
    {
        $this->jumlah_galon = min(20, max(1, $jumlah));
    }

    public function pesanLagi(): void
    {
        $this->pesananBerhasil = null;
        $this->jumlah_galon = 1;
        $this->catatan = '';
        $this->metode_bayar = 'tunai';
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
        $rules = [
            'cabang_id' => ['required', 'integer', Rule::exists('cabang', 'id')->where('is_active', true)],
            'jumlah_galon' => ['required', 'integer', 'min:1', 'max:20'],
            'alamat_antar' => ['required', 'string', 'min:5', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'metode_bayar' => ['required', 'string', Rule::enum(MetodePembayaran::class)],
        ];

        if ($this->needsPhone) {
            $rules['phone'] = ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{8,13}$/'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'cabang_id.required' => __('Pilih cabang tujuan pengantaran.'),
            'cabang_id.exists' => __('Cabang yang dipilih tidak aktif atau tidak ditemukan.'),
            'jumlah_galon.required' => __('Jumlah galon wajib diisi.'),
            'jumlah_galon.integer' => __('Jumlah galon harus berupa bilangan bulat.'),
            'jumlah_galon.min' => __('Jumlah galon minimal 1.'),
            'jumlah_galon.max' => __('Jumlah galon maksimal 20 per pesanan online.'),
            'alamat_antar.required' => __('Alamat pengantaran wajib diisi.'),
            'alamat_antar.min' => __('Alamat pengantaran minimal 5 karakter.'),
            'alamat_antar.max' => __('Alamat pengantaran maksimal 500 karakter.'),
            'catatan.max' => __('Catatan maksimal 255 karakter.'),
            'metode_bayar.required' => __('Pilih metode pembayaran.'),
            'metode_bayar.Illuminate\\Validation\\Rules\\Enum' => __('Metode pembayaran tidak sah.'),
            'phone.required' => __('Nomor HP wajib dilengkapi sebelum memesan.'),
            'phone.regex' => __('Format nomor HP tidak sah (contoh: 08123456789 atau 62812...).'),
        ];
    }

    public function simpan(): void
    {
        // 1. Otorisasi ulang pada setiap request simpan (server-side)
        $pelanggan = $this->authorizePelanggan();

        // 2. Pengaman double-submit: Cache::lock per pelanggan selama 10 detik
        $lock = Cache::lock('order-pelanggan-' . $pelanggan->id, 10);
        if (! $lock->get()) {
            throw ValidationException::withMessages([
                '_lock' => __('Mohon tunggu sebentar, pesanan sebelumnya sedang diproses.'),
            ]);
        }

        try {
            // 3. Validasi input form
            $validated = $this->validate();

            // 4. Proses nomor HP untuk akun Google
            if ($this->needsPhone) {
                $phoneService = app(CustomerPhoneService::class);
                $normalizedPhone = $phoneService->normalize($validated['phone']);
                $phoneService->ensurePhoneIsUnique($normalizedPhone, $pelanggan->id);
            } else {
                $normalizedPhone = $pelanggan->no_hp;
            }

            // 5. Hitung total di SERVER murni dari konfigurasi; nilai browser diabaikan
            $jumlah = (int) $validated['jumlah_galon'];
            $hargaSatuan = $this->hargaSatuan;
            $totalHargaServer = $jumlah * $hargaSatuan;
            $metodeEnum = MetodePembayaran::from($validated['metode_bayar']);
            $cabang = Cabang::findOrFail((int) $validated['cabang_id']);

            // 6. Simpan pesanan dan catat status awal dalam satu transaksi DB
            $pesanan = DB::transaction(function () use ($pelanggan, $validated, $totalHargaServer, $metodeEnum, $jumlah, $normalizedPhone, $cabang): Transaksi {
                // Simpan no_hp ke profil pelanggan jika baru dilengkapi (akun Google)
                if ($this->needsPhone) {
                    $pelanggan->no_hp = $normalizedPhone;
                    $pelanggan->save();
                }

                // Simpan/perbarui alamat ke profil pelanggan jika berubah atau masih kosong
                $alamatBaru = trim($validated['alamat_antar']);
                if ($pelanggan->alamat === null || $pelanggan->alamat === '' || $pelanggan->alamat !== $alamatBaru) {
                    $pelanggan->alamat = $alamatBaru;
                    $pelanggan->save();
                }

                $transaksi = new Transaksi();
                $transaksi->cabang_id = $cabang->id;
                $transaksi->pelanggan_id = $pelanggan->id;
                $transaksi->kurir_id = null; // Kurir ditugaskan di US-12
                $transaksi->jenis_kanal = Kanal::Cod;
                $transaksi->jenis_item = JenisItem::IsiUlang; // Online hanya isi ulang (PRD §5)
                $transaksi->jumlah_galon = $jumlah;
                $transaksi->total_harga = $totalHargaServer;
                $transaksi->metode_bayar = $metodeEnum;
                $transaksi->status = StatusTransaksi::Diproses;
                $transaksi->tanggal = CarbonImmutable::today();
                $transaksi->nama_pemesan = trim($pelanggan->nama);
                $transaksi->no_hp_pemesan = (string) $normalizedPhone;
                $transaksi->alamat_antar = $alamatBaru;
                $transaksi->dicatat_oleh = null; // Dipesan sendiri oleh pelanggan
                $transaksi->diverifikasi_oleh = null;
                $transaksi->save();

                // Catat status awal di riwayat_status
                app(StatusPesananService::class)->catatStatusAwal($transaksi, $pelanggan);

                return $transaksi;
            });

        } finally {
            // Pastikan lock selalu dilepas meski terjadi exception
            $lock->release();
        }

        // 7. Siapkan data konfirmasi
        $this->pesananBerhasil = [
            'id' => (int) $pesanan->id,
            'nama_pemesan' => (string) $pesanan->nama_pemesan,
            'alamat_antar' => (string) $pesanan->alamat_antar,
            'cabang' => $cabang->nama,
            'jumlah' => $pesanan->jumlah_galon,
            'harga_satuan' => $hargaSatuan,
            'total' => $totalHargaServer,
            'metode' => $pesanan->metode_bayar->label(),
            'metode_kode' => $pesanan->metode_bayar->value,
            'status' => $pesanan->status->label(),
            'tanggal' => CarbonImmutable::today()->translatedFormat('d F Y'),
        ];

        // 8. Reset form untuk pesanan berikutnya
        $this->alamat_antar = $pelanggan->fresh()?->alamat ?? '';
        $this->jumlah_galon = 1;
        $this->catatan = '';
        $this->metode_bayar = 'tunai';
        $this->phone = '';
        $this->cabang_id = null;
    }
}; ?>

<div class="py-8 sm:py-12">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        {{-- JIKA PESANAN BERHASIL (KARTU KONFIRMASI ELEGAN) --}}
        @if ($pesananBerhasil !== null)
            <div class="mx-auto max-w-xl rounded-3xl border border-success/30 bg-white p-6 sm:p-8 shadow-card" data-test="konfirmasi-berhasil">
                <div class="text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-success-soft text-success mb-3 shadow-2xs">
                        <flux:icon.check variant="mini" class="size-7" />
                    </div>
                    <span class="rounded-full bg-success-soft px-3.5 py-1 text-xs font-bold text-success uppercase tracking-wider">
                        {{ __('Pesanan Berhasil Dibuat') }}
                    </span>
                    <h2 class="mt-2 text-2xl font-bold text-ink">Pesanan #{{ $pesananBerhasil['id'] }} Diterima</h2>
                    <p class="mt-1 text-xs text-muted">
                        {{ __('Kurir depot kami akan segera memproses dan mengantarkan galon ke alamat Anda.') }}
                    </p>
                </div>

                <div class="mt-6 rounded-2xl bg-surface p-5 text-xs space-y-3 border border-line">
                    <div class="flex justify-between items-center">
                        <span class="text-muted">{{ __('Cabang Depot') }}</span>
                        <span class="font-bold text-ink text-sm">{{ e($pesananBerhasil['cabang']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted">{{ __('Jumlah Isi Ulang') }}</span>
                        <span class="font-bold text-ink">{{ $pesananBerhasil['jumlah'] }} galon</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-muted">{{ __('Metode Pembayaran') }}</span>
                        <span class="font-medium text-ink">{{ e($pesananBerhasil['metode']) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2.5 border-t border-line/60">
                        <span class="font-bold text-ink">{{ __('Total Bayar saat Tiba') }}</span>
                        <span class="text-lg font-extrabold text-primary">{{ $this->rupiah($pesananBerhasil['total']) }}</span>
                    </div>
                    <div class="pt-2.5 border-t border-line/60">
                        <span class="text-muted block mb-1">{{ __('Alamat Pengantaran:') }}</span>
                        <span class="text-ink font-medium leading-relaxed">{{ e($pesananBerhasil['alamat_antar']) }}</span>
                    </div>

                    {{-- [US-14] Tampilan QR Statis pada Konfirmasi Berhasil bila memilih metode QR --}}
                    @if ($pesananBerhasil['metode_kode'] === 'qr')
                        <div class="pt-3 border-t border-line/60 space-y-3" data-test="konfirmasi-qr-statis">
                            @if ($this->qrCodeUrl !== null)
                                <div class="rounded-xl border border-line bg-white p-3.5 text-center shadow-2xs">
                                    <span class="text-[11px] font-bold text-ink block mb-2">{{ __('Pindai Kode QRIS Depot') }}</span>
                                    <div class="inline-block rounded-xl border border-line p-2 bg-white">
                                        <img
                                            src="{{ $this->qrCodeUrl }}"
                                            alt="{{ __('Kode QRIS Statis Depot') }}"
                                            class="mx-auto max-h-60 sm:max-h-64 w-auto object-contain rounded-lg"
                                            data-test="konfirmasi-img-qr-statis"
                                        />
                                    </div>
                                    <div class="mt-2">
                                        <span class="text-[11px] text-muted block">{{ __('Total tagihan:') }}</span>
                                        <span class="text-base font-extrabold text-primary">
                                            {{ $this->rupiah($pesananBerhasil['total']) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-info/20 bg-info-soft/40 p-3 text-[11px] text-muted flex items-start gap-2">
                                    <flux:icon.information-circle variant="mini" class="size-4 text-info shrink-0 mt-0.5" />
                                    <span>{{ __('Pembayaran QR tidak diverifikasi otomatis. Simpan bukti transfer di ponsel Anda untuk diperlihatkan kepada kurir saat pesanan tiba.') }}</span>
                                </div>
                            @else
                                <div class="rounded-xl border border-warning/30 bg-warning-soft/40 p-3 text-[11px] text-muted flex items-start gap-2" data-test="konfirmasi-empty-qr-statis">
                                    <flux:icon.exclamation-triangle variant="mini" class="size-4 text-warning shrink-0 mt-0.5" />
                                    <span>{{ __('QR pembayaran belum tersedia. Silakan pilih Tunai atau hubungi depot.') }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <button
                    wire:click="pesanLagi"
                    class="mt-6 w-full rounded-xl bg-primary py-3.5 text-sm font-bold text-white shadow-card transition hover:bg-primary-strong"
                >
                    {{ __('+ Buat Pesanan Baru') }}
                </button>
            </div>

        {{-- FORM PEMESANAN DENGAN DESAIN MODERN & NYAMAN --}}
        @else
            {{-- Header Ringkas & Elegan --}}
            <div class="mb-8">
                <div class="inline-flex items-center gap-2 rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold text-primary mb-2">
                    <span class="size-2 rounded-full bg-primary"></span>
                    <span>{{ __('Layanan Antar Air Galon (COD)') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                    {{ __('Pemesanan Air Minum Galon') }}
                </h1>
                <p class="mt-1.5 text-sm text-muted">
                    {{ __('Pesan langsung dari depot resmi CV. Aufa. Bayar saat galon tiba di rumah.') }}
                </p>
            </div>

            <form wire:submit="simpan">

                {{-- Alert Lock (Jika klik ganda) --}}
                @error('_lock')
                    <div class="mb-6 rounded-2xl bg-danger-soft p-4 text-xs text-danger border border-danger/20 flex items-center gap-2" role="alert">
                        <flux:icon.exclamation-triangle variant="mini" class="size-4 shrink-0" />
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    {{-- ==================== FORM UTAMA (KIRI) ==================== --}}
                    <div class="lg:col-span-7 space-y-6">

                        {{-- KARTU 1: PILIHAN GALON & JUMLAH --}}
                        <div class="rounded-3xl border border-line bg-white p-6 shadow-2xs">
                            <div class="flex items-center justify-between pb-4 border-b border-line mb-5">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex size-8 items-center justify-center rounded-xl bg-primary-soft text-primary">
                                        <flux:icon.shopping-bag variant="mini" class="size-4" />
                                    </div>
                                    <span class="font-bold text-ink text-sm">{{ __('1. Jumlah Galon Isi Ulang') }}</span>
                                </div>
                                <span class="text-xs font-bold text-primary bg-primary-soft px-2.5 py-1 rounded-lg">
                                    {{ $this->rupiah($this->hargaSatuan) }} / galon
                                </span>
                            </div>

                            {{-- Stepper Kuantitas Galon --}}
                            <div class="rounded-2xl bg-surface p-4 border border-line/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h2 class="font-bold text-ink text-sm">{{ __('Air Minum Isi Ulang 19 Liter') }}</h2>
                                    <p class="text-xs text-muted mt-0.5">{{ __('Galon dibersihkan dan diisi ulang higienis') }}</p>
                                </div>

                                <div class="flex items-center gap-3 self-end sm:self-center">
                                    <button
                                        type="button"
                                        wire:click="kurangiJumlah"
                                        class="flex size-10 items-center justify-center rounded-xl border border-line bg-white text-ink shadow-2xs transition hover:bg-surface-low disabled:opacity-40"
                                        :disabled="{{ $jumlah_galon <= 1 ? 'true' : 'false' }}"
                                        data-test="btn-kurangi"
                                        title="Kurangi"
                                    >
                                        <flux:icon.minus variant="mini" class="size-4" />
                                    </button>

                                    <div class="w-12 text-center">
                                        <span class="text-2xl font-extrabold text-ink" data-test="jumlah-galon">
                                            {{ $jumlah_galon }}
                                        </span>
                                        <span class="text-[10px] text-muted block -mt-1">{{ __('galon') }}</span>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="tambahJumlah"
                                        class="flex size-10 items-center justify-center rounded-xl bg-primary text-white shadow-2xs transition hover:bg-primary-strong disabled:opacity-40"
                                        :disabled="{{ $jumlah_galon >= 20 ? 'true' : 'false' }}"
                                        data-test="btn-tambah"
                                        title="Tambah"
                                    >
                                        <flux:icon.plus variant="mini" class="size-4" />
                                    </button>
                                </div>
                            </div>

                            {{-- Pintasan Kuantitas Cepat --}}
                            <div class="mt-4 flex items-center justify-between flex-wrap gap-2 text-xs">
                                <span class="text-muted">{{ __('Pilih cepat:') }}</span>
                                <div class="flex items-center gap-1.5">
                                    @foreach ([1, 2, 3, 5, 10] as $preset)
                                        <button
                                            type="button"
                                            wire:click="setJumlah({{ $preset }})"
                                            class="rounded-xl px-3 py-1.5 text-xs font-semibold transition
                                                {{ $jumlah_galon === $preset ? 'bg-primary text-white shadow-2xs' : 'bg-white border border-line text-ink hover:bg-surface-low' }}"
                                        >
                                            {{ $preset }} galon
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @error('jumlah_galon')
                                <p class="mt-2 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KARTU 2: TUJUAN PENGANTARAN --}}
                        <div class="rounded-3xl border border-line bg-white p-6 shadow-2xs">
                            <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-5">
                                <div class="flex size-8 items-center justify-center rounded-xl bg-primary-soft text-primary">
                                    <flux:icon.map-pin variant="mini" class="size-4" />
                                </div>
                                <span class="font-bold text-ink text-sm">{{ __('2. Lokasi & Alamat Pengantaran') }}</span>
                            </div>

                            {{-- Alert Lengkapi No HP untuk Akun Google --}}
                            @if ($this->needsPhone)
                                <div class="mb-5 rounded-2xl border border-warning/30 bg-warning-soft/70 p-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <flux:icon.phone variant="mini" class="size-4 text-warning" />
                                        <span class="text-xs font-bold text-ink">{{ __('Nomor HP Diperlukan') }}</span>
                                    </div>
                                    <p class="text-[11px] text-muted mb-3">
                                        {{ __('Lengkapi nomor HP/WhatsApp aktif agar kurir dapat menghubungi Anda saat tiba di lokasi.') }}
                                    </p>
                                    <flux:field>
                                        <flux:label for="phone">{{ __('Nomor WhatsApp / HP Aktif') }} <span class="text-danger">*</span></flux:label>
                                        <flux:input
                                            id="phone"
                                            wire:model="phone"
                                            type="tel"
                                            placeholder="Contoh: 081234567890"
                                            autocomplete="tel"
                                            data-test="input-phone"
                                        />
                                        <flux:error name="phone" />
                                    </flux:field>
                                </div>
                            @endif

                            <div class="space-y-4">
                                {{-- Cabang Depot --}}
                                <flux:field>
                                    <flux:label for="cabang_id">
                                        {{ __('Pilih Cabang Depot Terdekat') }} <span class="text-danger">*</span>
                                    </flux:label>
                                    <flux:select
                                        id="cabang_id"
                                        wire:model="cabang_id"
                                        data-test="select-cabang"
                                    >
                                        <flux:select.option value="">{{ __('-- Pilih Depot Pengantar --') }}</flux:select.option>
                                        @foreach ($this->daftarCabang as $cabang)
                                            <flux:select.option value="{{ $cabang->id }}">
                                                {{ $cabang->nama }} — {{ $cabang->alamat }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="cabang_id" />
                                </flux:field>

                                {{-- Alamat Pengantaran Lengkap --}}
                                <flux:field>
                                    <flux:label for="alamat_antar">
                                        {{ __('Alamat Lengkap Pengantaran') }} <span class="text-danger">*</span>
                                    </flux:label>
                                    <flux:textarea
                                        id="alamat_antar"
                                        wire:model="alamat_antar"
                                        rows="3"
                                        placeholder="{{ __('Contoh: Jl. Sudirman No. 12, RT 02 RW 03, Kelurahan Belakang Tangsi') }}"
                                        data-test="input-alamat"
                                    />
                                    <flux:error name="alamat_antar" />
                                </flux:field>

                                {{-- Catatan Kurir --}}
                                <flux:field>
                                    <flux:label for="catatan">
                                        {{ __('Catatan Patokan Lokasi') }}
                                        <span class="text-muted font-normal text-[11px]">({{ __('Opsional') }})</span>
                                    </flux:label>
                                    <flux:input
                                        id="catatan"
                                        wire:model="catatan"
                                        placeholder="{{ __('Contoh: Pagar hitam, dekat warung Bu Ani, letakkan di teras') }}"
                                        data-test="input-catatan"
                                    />
                                    <flux:error name="catatan" />
                                </flux:field>
                            </div>
                        </div>

                        {{-- KARTU 3: METODE PEMBAYARAN SAAT TIBA --}}
                        <div class="rounded-3xl border border-line bg-white p-6 shadow-2xs">
                            <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-5">
                                <div class="flex size-8 items-center justify-center rounded-xl bg-primary-soft text-primary">
                                    <flux:icon.credit-card variant="mini" class="size-4" />
                                </div>
                                <div>
                                    <span class="font-bold text-ink text-sm block">{{ __('3. Metode Pembayaran') }}</span>
                                    <span class="text-[11px] text-muted">{{ __('Bayar langsung saat air galon tiba di tempat') }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                {{-- Tunai --}}
                                <button
                                    type="button"
                                    wire:click="pilihMetode('tunai')"
                                    class="flex items-center gap-3.5 rounded-2xl border-2 p-4 text-left transition
                                        {{ $metode_bayar === 'tunai' ? 'border-primary bg-primary-soft/30 shadow-2xs' : 'border-line bg-white hover:border-primary/40' }}"
                                    data-test="btn-metode-tunai"
                                >
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $metode_bayar === 'tunai' ? 'bg-primary text-white' : 'bg-surface text-muted' }}">
                                        <flux:icon.banknotes variant="mini" class="size-5" />
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-ink block">{{ __('Uang Tunai') }}</span>
                                        <span class="text-[11px] text-muted leading-snug block mt-0.5">{{ __('Bayar uang pas ke kurir') }}</span>
                                    </div>
                                </button>

                                {{-- QRIS --}}
                                <button
                                    type="button"
                                    wire:click="pilihMetode('qr')"
                                    class="flex items-center gap-3.5 rounded-2xl border-2 p-4 text-left transition
                                        {{ $metode_bayar === 'qr' ? 'border-primary bg-primary-soft/30 shadow-2xs' : 'border-line bg-white hover:border-primary/40' }}"
                                    data-test="btn-metode-qr"
                                >
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $metode_bayar === 'qr' ? 'bg-primary text-white' : 'bg-surface text-muted' }}">
                                        <flux:icon.qr-code variant="mini" class="size-5" />
                                    </div>
                                    <div>
                                        <span class="text-sm font-bold text-ink block">{{ __('QRIS Statis') }}</span>
                                        <span class="text-[11px] text-muted leading-snug block mt-0.5">{{ __('Scan QR kurir saat tiba') }}</span>
                                    </div>
                                </button>
                            </div>

                            @error('metode_bayar')
                                <p class="mt-2 text-xs text-danger">{{ $message }}</p>
                            @enderror

                            {{-- [US-14] Tampilan Gambar QR Statis bila metode QR dipilih --}}
                            @if ($metode_bayar === 'qr')
                                <div class="mt-5 pt-5 border-t border-line space-y-4" data-test="container-qr-statis">
                                    @if ($this->qrCodeUrl !== null)
                                        <div class="rounded-2xl border border-line bg-surface-low/50 p-4 sm:p-5 text-center">
                                            <div class="inline-block rounded-2xl border border-line bg-white p-3.5 shadow-2xs">
                                                <img
                                                    src="{{ $this->qrCodeUrl }}"
                                                    alt="{{ __('Kode QRIS Statis Depot CV. Aufa') }}"
                                                    class="mx-auto max-h-72 sm:max-h-80 w-auto object-contain rounded-xl"
                                                    data-test="img-qr-statis"
                                                />
                                            </div>

                                            <div class="mt-3">
                                                <span class="text-xs text-muted block">{{ __('Total tagihan:') }}</span>
                                                <span class="text-lg font-black text-primary tracking-tight" data-test="qr-nominal-bayar">
                                                    {{ $this->rupiah($this->totalHarga) }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="rounded-xl border border-info/20 bg-info-soft/40 p-3 text-xs text-ink flex items-start gap-2.5">
                                            <flux:icon.information-circle variant="mini" class="size-4 text-info shrink-0 mt-0.5" />
                                            <div class="text-[11px] leading-relaxed text-muted">
                                                <strong class="text-ink font-semibold">{{ __('Penting:') }}</strong>
                                                {{ __('Pembayaran QR tidak diverifikasi otomatis oleh sistem. Cukup simpan bukti transfer di ponsel Anda dan perlihatkan kepada kurir saat galon tiba di alamat.') }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-2xl border border-warning/30 bg-warning-soft/40 p-4 flex items-start gap-3" data-test="empty-qr-statis">
                                            <flux:icon.exclamation-triangle variant="mini" class="size-4 text-warning shrink-0 mt-0.5" />
                                            <div>
                                                <span class="text-xs font-bold text-ink block">{{ __('QR Pembayaran Belum Tersedia') }}</span>
                                                <span class="text-[11px] text-muted block mt-0.5">
                                                    {{ __('QR pembayaran belum tersedia. Silakan pilih Tunai atau hubungi depot.') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- ==================== PANEL RINGKASAN STICKY (KANAN) ==================== --}}
                    <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-4">

                        <div class="rounded-3xl border-2 border-primary/20 bg-white p-6 shadow-card">
                            <div class="flex items-center justify-between pb-4 border-b border-line mb-4">
                                <h3 class="font-bold text-ink text-sm">{{ __('Ringkasan Pesanan') }}</h3>
                                <span class="rounded-full bg-success-soft px-2.5 py-0.5 text-[10px] font-bold text-success uppercase">
                                    {{ __('Layanan COD') }}
                                </span>
                            </div>

                            {{-- Rincian Biaya --}}
                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted">{{ $jumlah_galon }}x {{ __('Air Galon Isi Ulang') }}</span>
                                    <span class="font-bold text-ink">{{ $this->rupiah($this->totalHarga) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-muted">
                                    <span>{{ __('Tarif per galon') }}</span>
                                    <span>{{ $this->rupiah($this->hargaSatuan) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-line/60">
                                    <span class="text-muted">{{ __('Biaya Pengantaran') }}</span>
                                    <span class="font-bold text-success">{{ __('Gratis') }}</span>
                                </div>
                            </div>

                            {{-- Total Pembayaran --}}
                            <div class="mt-5 pt-4 border-t-2 border-dashed border-line">
                                <span class="text-xs text-muted block">{{ __('Total Pembayaran:') }}</span>
                                <div class="mt-1 flex items-baseline justify-between">
                                    <span class="text-3xl font-black text-primary tracking-tight" data-test="total-harga">
                                        {{ $this->rupiah($this->totalHarga) }}
                                    </span>
                                    <span class="text-xs font-semibold text-ink bg-surface px-2.5 py-1 rounded-lg border border-line">
                                        {{ $metode_bayar === 'tunai' ? __('Uang Tunai') : __('QRIS') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Pengingat Galon Kosong --}}
                            <div class="mt-4 rounded-xl bg-surface-low p-3 text-[11px] text-muted flex items-start gap-2 border border-line/60">
                                <flux:icon.arrow-path variant="mini" class="size-3.5 text-primary shrink-0 mt-0.5" />
                                <span>{{ __('Siapkan') }} <strong class="text-ink">{{ $jumlah_galon }} {{ __('galon kosong') }}</strong> {{ __('untuk ditukar saat kurir tiba di alamat Anda.') }}</span>
                            </div>

                            {{-- Tombol Pesan Sekarang --}}
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 cursor-not-allowed"
                                class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-base font-bold text-white shadow-card transition hover:bg-primary-strong focus:outline-hidden focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-70"
                                data-test="btn-simpan"
                            >
                                <span wire:loading.remove>
                                    <flux:icon.shopping-cart variant="mini" class="size-5" />
                                </span>
                                <span wire:loading>
                                    <flux:icon.arrow-path variant="mini" class="size-5 animate-spin" />
                                </span>
                                <span wire:loading.remove>{{ __('Pesan Sekarang') }}</span>
                                <span wire:loading>{{ __('Memproses pesanan...') }}</span>
                            </button>

                            <div class="mt-3.5 text-center">
                                <a
                                    href="https://wa.me/{{ preg_replace('/\D+/', '', (string) config('services.admin_whatsapp_number', '6281200000000')) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-[11px] text-muted hover:text-primary transition inline-flex items-center gap-1"
                                >
                                    <flux:icon.chat-bubble-left-ellipsis variant="mini" class="size-3.5" />
                                    <span>{{ __('Butuh bantuan? Chat WhatsApp kami') }}</span>
                                </a>
                            </div>
                        </div>

                        {{-- Kartu Keunggulan Layanan --}}
                        <div class="rounded-2xl border border-line bg-white p-4 shadow-2xs space-y-2 text-xs text-muted">
                            <div class="flex items-center gap-2 text-ink font-semibold">
                                <flux:icon.shield-check variant="mini" class="size-4 text-primary shrink-0" />
                                <span>{{ __('Standar Resmi CV. Aufa') }}</span>
                            </div>
                            <p class="text-[11px] leading-relaxed">
                                {{ __('Galon dibersihkan dengan sikat mesin higienis dan dibilas air ozonisasi sebelum diisi ulang.') }}
                            </p>
                        </div>

                    </div>

                </div>

            </form>
        @endif

    </div>
</div>
