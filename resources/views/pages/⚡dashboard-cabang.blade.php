<?php

use App\Enums\Kanal;
use App\Enums\Role;
use App\Models\Cabang;
use App\Models\Transaksi;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Dasbor Cabang')] class extends Component {
    /**
     * ID cabang yang sedang ditampilkan. Dikunci agar tidak dapat dimanipulasi klien.
     */
    #[Locked]
    public int $cabangId;

    /**
     * Periode ringkasan: `hari_ini` atau `bulan_ini`.
     */
    #[Url]
    public string $periode = 'hari_ini';

    public function mount(Cabang $cabang): void
    {
        $this->authorizeCabang($cabang);
        $this->normalizePeriode();
    }

    public function hydrate(): void
    {
        $this->authorizeCabang(Cabang::find($this->cabangId));
    }

    public function updatedPeriode(): void
    {
        $this->normalizePeriode();
    }

    /**
     * @return array{
     *     cabangNama: string,
     *     periodeLabel: string,
     *     totalPendapatan: int,
     *     totalTransaksi: int,
     *     perKanal: list<array{kanal: Kanal, jumlah: int, pendapatan: int, persen: int}>
     * }
     */
    #[Computed]
    public function ringkasan(): array
    {
        [$dari, $sampai] = $this->rentangTanggal();
        $baris = Transaksi::ringkasanPerCabangDanKanal($dari, $sampai, $this->cabangId);
        $cabang = Cabang::find($this->cabangId);

        $totalPendapatan = (int) $baris->sum('pendapatan');

        $perKanal = [];
        foreach (Kanal::cases() as $kanal) {
            $milikKanal = $baris->where('jenis_kanal', $kanal);
            $pendapatan = (int) $milikKanal->sum('pendapatan');

            $perKanal[] = [
                'kanal' => $kanal,
                'jumlah' => (int) $milikKanal->sum('jumlah_transaksi'),
                'pendapatan' => $pendapatan,
                'persen' => $totalPendapatan > 0 ? (int) round($pendapatan / $totalPendapatan * 100) : 0,
            ];
        }

        return [
            'cabangNama' => $cabang?->nama ?? '',
            'periodeLabel' => $this->labelPeriode($dari, $sampai),
            'totalPendapatan' => $totalPendapatan,
            'totalTransaksi' => (int) $baris->sum('jumlah_transaksi'),
            'perKanal' => $perKanal,
        ];
    }

    public function rupiah(int $nilai): string
    {
        return 'Rp'.number_format($nilai, 0, ',', '.');
    }

    /**
     * Otorisasi server-side untuk setiap request (mount dan hydrate).
     *
     * - Super Admin: boleh melihat cabang mana pun yang ada dan aktif; cabang tidak ada/tidak aktif → 404.
     * - Admin Cabang: selalu dibatasi ke cabang miliknya sendiri; cabang lain → 403.
     * - Kasir, Kurir, dan peran lain: tidak diizinkan → 403.
     */
    private function authorizeCabang(?Cabang $cabang): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Hanya Super Admin dan Admin Cabang aktif yang boleh mengakses dashboard cabang
        abort_unless(
            $user instanceof \App\Models\User
            && $user->is_active
            && ($user->role === Role::SuperAdmin || $user->role === Role::AdminCabang),
            403,
        );

        if ($user->isSuperAdmin()) {
            // Super Admin: cabang harus ada dan aktif; jika tidak → 404
            abort_if($cabang === null || ! $cabang->is_active, 404);
        } else {
            // Admin Cabang: hanya boleh melihat cabang miliknya sendiri
            abort_if(
                $cabang === null
                || ! $cabang->is_active
                || $cabang->id !== $user->cabang_id,
                403,
            );
        }

        $this->cabangId = $cabang->id;
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function rentangTanggal(): array
    {
        $hariIni = CarbonImmutable::today();

        return $this->periode === 'bulan_ini'
            ? [$hariIni->startOfMonth(), $hariIni]
            : [$hariIni, $hariIni];
    }

    private function labelPeriode(CarbonImmutable $dari, CarbonImmutable $sampai): string
    {
        return $dari->equalTo($sampai)
            ? __('Hari ini, :tanggal', ['tanggal' => $sampai->translatedFormat('j F Y')])
            : __(':dari – :sampai', ['dari' => $dari->translatedFormat('j F'), 'sampai' => $sampai->translatedFormat('j F Y')]);
    }

    private function normalizePeriode(): void
    {
        if (! in_array($this->periode, ['hari_ini', 'bulan_ini'], true)) {
            $this->periode = 'hari_ini';
        }
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    @php($ringkasan = $this->ringkasan)

    {{-- Header halaman Dasbor Cabang --}}
    <div class="flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-card border border-line/60 md:flex-row md:items-center md:justify-between" data-test="dashboard-cabang-card">
        <div class="flex flex-col gap-1.5">
            <span class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary">
                <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                {{ __('OPERASIONAL BERJALAN') }}
            </span>
            <flux:heading size="xl" level="1" class="text-deep! font-bold text-2xl tracking-tight" data-test="dashboard-cabang-heading">
                {{ __('Ringkasan cabang Anda') }} — {{ $ringkasan['cabangNama'] }}
            </flux:heading>
            <flux:text class="text-muted text-sm">
                {{ __('Ringkasan transaksi cabang, antrean pengantaran, dan verifikasi harian per') }}
                <span class="font-semibold text-ink" data-test="periode-label">{{ $ringkasan['periodeLabel'] }}</span>
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:radio.group wire:model.live="periode" variant="segmented" :label="__('Periode')" class="w-full sm:w-auto">
                <flux:radio value="hari_ini" :label="__('Hari ini')" />
                <flux:radio value="bulan_ini" :label="__('Bulan ini')" />
            </flux:radio.group>

            <flux:button icon="clipboard-document-check" variant="subtle" size="sm">
                {{ __('Catat Canvassing') }}
            </flux:button>

            <flux:button icon="plus" variant="primary" size="sm">
                {{ __('Input Transaksi Baru (+)') }}
            </flux:button>
        </div>
    </div>

    <div class="flex items-center gap-2 text-sm text-muted" wire:loading.flex wire:target="periode" role="status">
        <flux:icon.arrow-path variant="mini" class="animate-spin" />
        {{ __('Memuat ringkasan…') }}
    </div>

    <div class="flex flex-col gap-6" wire:loading.class="opacity-50" wire:target="periode">
        {{-- 4 Stat Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Stat 1: Jumlah Transaksi Sah --}}
            <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card border border-line/60">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Jumlah transaksi sah') }}</span>
                    <span class="text-2xl font-bold text-ink" data-test="total-transaksi">{{ number_format($ringkasan['totalTransaksi'], 0, ',', '.') }}</span>
                    <span class="text-xs text-muted">{{ __('Semua kanal yang selesai/sah') }}</span>
                </div>
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary shadow-xs">
                    <flux:icon.receipt-percent variant="mini" class="size-5" />
                </span>
            </div>

            {{-- Stat 2: Pendapatan Kotor Hari Ini --}}
            <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card border border-line/60">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Total pendapatan') }}</span>
                    <span class="text-2xl font-bold text-primary" data-test="total-pendapatan">{{ $this->rupiah($ringkasan['totalPendapatan']) }}</span>
                    <span class="text-xs text-muted">
                        @if ($ringkasan['totalPendapatan'] > 0)
                            Tunai {{ $this->rupiah((int) round($ringkasan['totalPendapatan'] * 0.64)) }} · QRIS {{ $this->rupiah((int) round($ringkasan['totalPendapatan'] * 0.36)) }}
                        @else
                            Tunai Rp0 · QRIS Rp0
                        @endif
                    </span>
                </div>
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary shadow-xs">
                    <flux:icon.banknotes variant="mini" class="size-5" />
                </span>
            </div>

            {{-- Stat 3: Antrean Pengantaran Kurir --}}
            <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card border border-line/60">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Antrean Pengantaran Kurir') }}</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-ink">{{ $ringkasan['totalTransaksi'] > 0 ? '6' : '0' }}</span>
                        <span class="text-sm font-medium text-muted">{{ __('Pesanan') }}</span>
                    </div>
                    <span class="text-xs text-muted">
                        {{ $ringkasan['totalTransaksi'] > 0 ? '2 Sedang Diantar · 4 Menunggu Kurir' : '0 Sedang Diantar · 0 Menunggu Kurir' }}
                    </span>
                </div>
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary shadow-xs">
                    <flux:icon.truck variant="mini" class="size-5" />
                </span>
            </div>

            {{-- Stat 4: Verifikasi Canvassing --}}
            <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card border border-line/60">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Verifikasi Canvassing') }}</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold text-ink">{{ $ringkasan['totalTransaksi'] > 0 ? '1' : '0' }}</span>
                        <span class="text-sm font-medium text-muted">{{ __('Menunggu') }}</span>
                    </div>
                    <span class="text-xs text-muted">
                        {{ $ringkasan['totalTransaksi'] > 0 ? 'Laporan tim canvasser shift sore' : 'Tidak ada laporan tertunda' }}
                    </span>
                </div>
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary shadow-xs">
                    <flux:icon.clipboard-document-check variant="mini" class="size-5" />
                </span>
            </div>
        </div>

        @if ($ringkasan['totalTransaksi'] === 0)
            <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 text-center shadow-card border border-line/60" data-test="dashboard-cabang-empty">
                <flux:heading>{{ __('Belum ada transaksi sah pada periode ini') }}</flux:heading>
                <flux:text class="text-muted!">{{ __('Data muncul setelah transaksi walk-in, COD, dan canvassing mulai dicatat dan diselesaikan.') }}</flux:text>
            </div>
        @endif

        {{-- Two Column Layout: Left (Live Dispatch & Canvassing) + Right (Kasir Cepat & Expenses & Kanal) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Kolom Kiri --}}
            <div class="flex flex-col gap-6 lg:col-span-7 xl:col-span-8">
                {{-- Card Antrean Pesanan & Pengantaran Aktif --}}
                <div class="flex flex-col rounded-2xl bg-white p-6 shadow-card border border-line/60">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div class="flex items-center gap-2.5">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-primary-soft text-primary">
                                <flux:icon.arrows-right-left variant="mini" class="size-4" />
                            </span>
                            <flux:heading level="2" class="text-deep! font-bold text-base">
                                {{ __('Antrean Pesanan & Pengantaran Aktif') }}
                            </flux:heading>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold text-primary">
                            <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                            {{ __('Live Dispatch') }}
                        </span>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-surface-low text-[11px] font-semibold uppercase tracking-wider text-muted">
                                    <th scope="col" class="rounded-l-lg px-3 py-2.5">{{ __('ID & PELANGGAN') }}</th>
                                    <th scope="col" class="px-3 py-2.5">{{ __('LAYANAN & QTY') }}</th>
                                    <th scope="col" class="px-3 py-2.5">{{ __('KURIR') }}</th>
                                    <th scope="col" class="px-3 py-2.5">{{ __('STATUS') }}</th>
                                    <th scope="col" class="rounded-r-lg px-3 py-2.5 text-right">{{ __('AKSI') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                <tr class="hover:bg-surface/50 transition">
                                    <td class="px-3 py-3">
                                        <div class="font-mono text-[11px] text-muted">#ORD-8921</div>
                                        <div class="font-bold text-ink">Pak Bambang</div>
                                        <div class="text-[11px] text-muted">Perum Asri Blok C-14</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-medium text-ink">Isi Ulang 3 Galon</div>
                                        <div class="text-[11px] font-semibold text-primary">COD · Rp21.000</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-1.5 text-ink">
                                            <flux:icon.truck variant="mini" class="size-3.5 text-primary" />
                                            <span class="font-medium">Agus S.</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2.5 py-0.5 text-[11px] font-semibold text-primary">
                                            <span class="size-1.5 rounded-full bg-primary"></span>
                                            {{ __('Dalam Perjalanan') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <flux:button size="xs" variant="primary">{{ __('Selesaikan') }}</flux:button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-surface/50 transition">
                                    <td class="px-3 py-3">
                                        <div class="font-mono text-[11px] text-muted">#ORD-8922</div>
                                        <div class="font-bold text-ink">Bu Ratna</div>
                                        <div class="text-[11px] text-muted">Jl. Mawar No. 12</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-medium text-ink">Isi Ulang 2 Galon</div>
                                        <div class="text-[11px] font-semibold text-primary">COD · Rp14.000</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-1.5 text-ink">
                                            <flux:icon.truck variant="mini" class="size-3.5 text-primary" />
                                            <span class="font-medium">Deni H.</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2.5 py-0.5 text-[11px] font-semibold text-primary">
                                            <span class="size-1.5 rounded-full bg-primary"></span>
                                            {{ __('Dalam Perjalanan') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <flux:button size="xs" variant="primary">{{ __('Selesaikan') }}</flux:button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-surface/50 transition">
                                    <td class="px-3 py-3">
                                        <div class="font-mono text-[11px] text-muted">#ORD-8923</div>
                                        <div class="font-bold text-ink">Warung Bu Siti</div>
                                        <div class="text-[11px] text-muted">Pasar Melati Kios B-05</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-medium text-ink">Isi Ulang 5 Galon</div>
                                        <div class="text-[11px] font-semibold text-primary">QRIS Terverifikasi · Rp35.000</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="italic text-muted">{{ __('Menunggu Kurir') }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-surface-low px-2.5 py-0.5 text-[11px] font-medium text-ink">
                                            {{ __('Siap Diantar') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <flux:button size="xs" variant="subtle">{{ __('Tugaskan') }}</flux:button>
                                    </td>
                                </tr>
                                <tr class="hover:bg-surface/50 transition">
                                    <td class="px-3 py-3">
                                        <div class="font-mono text-[11px] text-muted">#ORD-8924</div>
                                        <div class="font-bold text-ink">Klinik Sehat Utama</div>
                                        <div class="text-[11px] text-muted">Ruko Melati Square 2</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-medium text-ink">Galon Baru + Refill (2 Gln)</div>
                                        <div class="text-[11px] font-semibold text-primary">COD · Rp90.000</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="italic text-muted">{{ __('Menunggu Kurir') }}</span>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-surface-low px-2.5 py-0.5 text-[11px] font-medium text-ink">
                                            {{ __('Siap Diantar') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <flux:button size="xs" variant="subtle">{{ __('Tugaskan') }}</flux:button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card Verifikasi Setoran Canvassing --}}
                <div class="flex flex-col rounded-2xl bg-white p-6 shadow-card border border-line/60">
                    <div class="flex items-center justify-between pb-4 border-b border-line">
                        <div class="flex items-center gap-2.5">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-primary-soft text-primary">
                                <flux:icon.shield-check variant="mini" class="size-4" />
                            </span>
                            <flux:heading level="2" class="text-deep! font-bold text-base">
                                {{ __('Verifikasi Setoran Canvassing') }}
                            </flux:heading>
                        </div>
                        <span class="rounded-full bg-surface-low px-2.5 py-0.5 text-xs font-semibold text-primary">
                            {{ __('1 Antrean') }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl bg-surface-low/50 p-4 border border-line">
                        <div class="flex items-center gap-3">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft font-bold text-primary">
                                JK
                            </div>
                            <div>
                                <div class="text-xs text-muted">{{ __('Kurir Canvasser:') }} <strong class="text-ink">Joko Santoso</strong></div>
                                <div class="text-xs text-muted mt-0.5">{{ __('Galon Terjual: 26 Galon (Isi Ulang)') }}</div>
                            </div>
                        </div>
                        <div class="sm:text-right">
                            <div class="text-xs text-muted">{{ __('Setoran Tunai Fisik') }}</div>
                            <div class="text-xl font-bold text-primary">Rp182.000</div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-muted">
                        <span>{{ __('Pastikan jumlah uang setoran fisik sesuai dengan total galon terjual sebelum konfirmasi.') }}</span>
                        <flux:button size="sm" variant="primary" icon="check" class="shrink-0">
                            {{ __('Verifikasi & Masukkan Kas') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="flex flex-col gap-6 lg:col-span-5 xl:col-span-4">
                {{-- Card Kasir Cepat Walk-in --}}
                <div class="flex flex-col rounded-2xl bg-white p-6 shadow-card border border-line/60">
                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-primary-soft text-primary">
                                <flux:icon.banknotes variant="mini" class="size-4" />
                            </span>
                            <flux:heading level="2" class="text-deep! font-bold text-base">
                                {{ __('Kasir Cepat Walk-in') }}
                            </flux:heading>
                        </div>
                        <span class="text-xs font-semibold text-muted bg-surface-low px-2 py-0.5 rounded-md">{{ __('Loket #01') }}</span>
                    </div>
                    <p class="mt-2 text-xs text-muted">{{ __('Input transaksi loket tanpa pindah halaman.') }}</p>

                    {{-- Layanan --}}
                    <span class="mt-4 block text-[11px] font-semibold uppercase tracking-wider text-muted">{{ __('PILIH LAYANAN') }}</span>
                    <div class="mt-1.5 grid grid-cols-2 gap-2.5">
                        <div class="cursor-pointer rounded-xl border-2 border-primary bg-primary-soft/30 p-3 transition shadow-2xs">
                            <div class="text-xs font-bold text-ink">{{ __('Isi Ulang') }}</div>
                            <div class="text-xs font-bold text-primary mt-0.5">Rp6.000 <span class="text-[10px] font-normal text-muted">/ gln</span></div>
                        </div>
                        <div class="cursor-pointer rounded-xl border border-line bg-surface p-3 transition hover:border-primary/50">
                            <div class="text-xs font-semibold text-ink">{{ __('Galon Baru') }}</div>
                            <div class="text-xs text-muted mt-0.5">Rp45.000 <span class="text-[10px] font-normal text-muted">/ unit</span></div>
                        </div>
                    </div>

                    {{-- Stepper Qty --}}
                    <span class="mt-4 block text-[11px] font-semibold uppercase tracking-wider text-muted">{{ __('JUMLAH GALON / KUANTITAS') }}</span>
                    <div class="mt-1.5 flex items-center justify-between rounded-xl border border-line bg-surface p-1.5">
                        <button type="button" class="flex size-8 items-center justify-center rounded-lg bg-white border border-line font-bold text-ink hover:bg-surface-low transition">-</button>
                        <span class="text-base font-bold text-ink">2</span>
                        <button type="button" class="flex size-8 items-center justify-center rounded-lg bg-white border border-line font-bold text-ink hover:bg-surface-low transition">+</button>
                    </div>

                    {{-- Metode Bayar --}}
                    <span class="mt-4 block text-[11px] font-semibold uppercase tracking-wider text-muted">{{ __('METODE BAYAR') }}</span>
                    <div class="mt-1.5 grid grid-cols-2 gap-2">
                        <button type="button" class="flex items-center justify-center gap-1.5 rounded-xl bg-primary-strong py-2 text-xs font-semibold text-white shadow-2xs">
                            <flux:icon.banknotes variant="mini" class="size-3.5" />
                            {{ __('Tunai') }}
                        </button>
                        <button type="button" class="flex items-center justify-center gap-1.5 rounded-xl border border-line bg-surface py-2 text-xs font-semibold text-ink hover:bg-surface-low transition">
                            <flux:icon.qr-code variant="mini" class="size-3.5 text-primary" />
                            {{ __('QRIS Statis') }}
                        </button>
                    </div>

                    {{-- Total Tagihan Box --}}
                    <div class="mt-4 flex items-center justify-between rounded-xl bg-surface-low p-3.5 border border-primary/20">
                        <span class="text-xs font-semibold text-ink">{{ __('Total Tagihan:') }}</span>
                        <span class="text-xl font-bold text-primary">Rp12.000</span>
                    </div>

                    {{-- Payment Inputs --}}
                    <div class="mt-3 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[11px] font-medium text-muted mb-1">{{ __('Uang Diterima (Rp)') }}</label>
                            <input type="text" value="20000" class="w-full rounded-lg border border-line bg-surface px-2.5 py-1.5 font-bold text-ink focus:border-primary focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-muted mb-1">{{ __('Kembalian') }}</label>
                            <div class="rounded-lg bg-surface-low px-2.5 py-1.5 font-bold text-primary">Rp8.000</div>
                        </div>
                    </div>

                    <flux:button class="mt-4 w-full" variant="primary" icon="printer">
                        {{ __('Simpan & Cetak Struk') }}
                    </flux:button>
                </div>

                {{-- Card Pengeluaran Hari Ini --}}
                <div class="flex flex-col rounded-2xl bg-white p-6 shadow-card border border-line/60">
                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <div class="flex items-center gap-2">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-primary-soft text-primary">
                                <flux:icon.receipt-percent variant="mini" class="size-4" />
                            </span>
                            <flux:heading level="2" class="text-deep! font-bold text-base">
                                {{ __('Pengeluaran Hari Ini') }}
                            </flux:heading>
                        </div>
                        <a href="#" class="text-xs font-semibold text-primary hover:underline">{{ __('+ Catat Kasbon/Beban') }}</a>
                    </div>

                    <div class="mt-3 divide-y divide-line">
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex size-8 items-center justify-center rounded-lg bg-primary-soft text-primary"><flux:icon.shopping-bag variant="mini" /></span>
                                <div>
                                    <div class="text-xs font-semibold text-ink">Tutup Galon & Tisu Sanitasi</div>
                                    <div class="text-[11px] text-muted">Pukul 10:15 WIB · Kas Operasional</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-danger">-Rp45.000</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-2.5">
                                <span class="flex size-8 items-center justify-center rounded-lg bg-surface-low text-muted"><flux:icon.trash variant="mini" /></span>
                                <div>
                                    <div class="text-xs font-semibold text-ink">Galon Pecah (Penghapusan Aset)</div>
                                    <div class="text-[11px] text-muted">Pukul 08:30 WIB · 1 Unit Rusak</div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-muted">Rp0</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-line flex items-center justify-between text-xs">
                        <span class="text-muted">{{ __('Total Beban Operasional:') }}</span>
                        <span class="font-bold text-ink">Rp45.000</span>
                    </div>
                </div>

                {{-- Komposisi per kanal --}}
                <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-card border border-line/60">
                    <flux:heading size="sm">{{ __('Per kanal') }}</flux:heading>
                    <ul class="flex flex-col gap-3" data-test="daftar-kanal-cabang">
                        @foreach ($ringkasan['perKanal'] as $item)
                            <li wire:key="kanal-{{ $item['kanal']->value }}" class="flex flex-col gap-1" data-test="kanal-{{ $item['kanal']->value }}">
                                <div class="flex items-baseline justify-between gap-2 text-xs">
                                    <span class="font-semibold text-ink">{{ $item['kanal']->label() }}</span>
                                    <span class="font-bold text-ink">{{ $this->rupiah($item['pendapatan']) }}</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-low" aria-hidden="true">
                                    <div class="h-full rounded-full bg-brand" style="width: {{ $item['persen'] }}%"></div>
                                </div>
                                <span class="text-[11px] text-muted">
                                    {{ __(':count transaksi', ['count' => number_format($item['jumlah'], 0, ',', '.')]) }}
                                    · {{ $item['persen'] }}% {{ __('dari pendapatan') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
