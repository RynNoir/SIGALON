<?php

use App\Enums\Kanal;
use App\Models\Cabang;
use App\Models\Transaksi;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Dasbor')] class extends Component {
    /**
     * Periode ringkasan: `hari_ini` atau `bulan_ini`.
     */
    #[Url]
    public string $periode = 'hari_ini';

    public function mount(): void
    {
        $this->normalizePeriode();
    }

    public function updatedPeriode(): void
    {
        $this->normalizePeriode();
    }

    #[Computed]
    public function isSuperAdmin(): bool
    {
        return (bool) Auth::user()?->isSuperAdmin();
    }

    /**
     * Ringkasan lintas cabang (US-04). Hanya dihitung untuk Super Admin; peran lain tidak pernah menerima angka cabang lain.
     *
     * @return array{
     *     periodeLabel: string,
     *     totalPendapatan: int,
     *     totalTransaksi: int,
     *     perKanal: list<array{kanal: Kanal, jumlah: int, pendapatan: int, persen: int}>,
     *     perCabang: list<array{id: int, nama: string, aktif: bool, kanal: array<string, array{jumlah: int, pendapatan: int}>, jumlah: int, pendapatan: int}>
     * }|null
     */
    #[Computed]
    public function ringkasan(): ?array
    {
        if (! $this->isSuperAdmin) {
            return null;
        }

        [$dari, $sampai] = $this->rentangTanggal();
        $baris = Transaksi::ringkasanPerCabangDanKanal($dari, $sampai);

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

        $perCabang = Cabang::query()
            ->orderBy('nama')
            ->get(['id', 'nama', 'is_active'])
            ->map(function (Cabang $cabang) use ($baris): array {
                $milikCabang = $baris->where('cabang_id', $cabang->id);
                $kanal = [];

                foreach (Kanal::cases() as $item) {
                    $sel = $milikCabang->firstWhere('jenis_kanal', $item);
                    $kanal[$item->value] = [
                        'jumlah' => $sel['jumlah_transaksi'] ?? 0,
                        'pendapatan' => $sel['pendapatan'] ?? 0,
                    ];
                }

                return [
                    'id' => $cabang->id,
                    'nama' => $cabang->nama,
                    'aktif' => $cabang->is_active,
                    'kanal' => $kanal,
                    'jumlah' => (int) $milikCabang->sum('jumlah_transaksi'),
                    'pendapatan' => (int) $milikCabang->sum('pendapatan'),
                ];
            })
            ->values()
            ->all();

        return [
            'periodeLabel' => $this->labelPeriode($dari, $sampai),
            'totalPendapatan' => $totalPendapatan,
            'totalTransaksi' => (int) $baris->sum('jumlah_transaksi'),
            'perKanal' => $perKanal,
            'perCabang' => $perCabang,
        ];
    }

    public function rupiah(int $nilai): string
    {
        return 'Rp'.number_format($nilai, 0, ',', '.');
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
    @if ($this->ringkasan === null)
        @php
            $currentUser = auth()->user();
            $cabang = $currentUser?->cabang;
            $canOpen = $currentUser?->role === \App\Enums\Role::AdminCabang && $cabang !== null && $cabang->is_active;
        @endphp
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card md:flex-row md:items-center md:justify-between" data-test="dashboard-cabang-placeholder">
            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Dasbor') }}</span>
                <flux:heading size="xl" level="1" class="text-deep!">{{ __('Ringkasan cabang Anda') }}</flux:heading>
                @if ($canOpen)
                    <flux:text class="text-muted!">
                        {{ __('Ringkasan transaksi cabang dapat diakses melalui tombol di sebelah kanan.') }}
                    </flux:text>
                @else
                    <flux:text class="text-muted!" data-test="pesan-cabang-tidak-aktif">
                        {{ __('Akun belum terhubung ke cabang aktif atau tidak memiliki akses ringkasan dasbor.') }}
                    </flux:text>
                @endif
            </div>

            @if ($canOpen)
                <flux:button
                    :href="route('dashboard.cabang', $cabang)"
                    icon="arrow-top-right-on-square"
                    variant="primary"
                    wire:navigate
                    data-test="buka-dashboard-cabang"
                >
                    {{ __('Buka dashboard cabang') }}
                </flux:button>
            @endif
        </div>
    @else
        @php($ringkasan = $this->ringkasan)

        {{-- Header halaman --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card md:flex-row md:items-end md:justify-between">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Monitoring seluruh cabang') }}</span>
                <flux:heading size="xl" level="1" class="text-deep!">{{ __('Dasbor Super Admin') }}</flux:heading>
                <flux:text class="text-muted!">
                    {{ __('Pendapatan dan transaksi sah (walk-in & COD selesai, canvassing tervalidasi).') }}
                    <span class="font-medium text-ink" data-test="periode-label">{{ $ringkasan['periodeLabel'] }}</span>
                </flux:text>
            </div>

            <flux:radio.group wire:model.live="periode" variant="segmented" :label="__('Periode')" class="md:w-64">
                <flux:radio value="hari_ini" :label="__('Hari ini')" />
                <flux:radio value="bulan_ini" :label="__('Bulan ini')" />
            </flux:radio.group>
        </div>

        <div class="flex items-center gap-2 text-sm text-muted" wire:loading.flex wire:target="periode" role="status">
            <flux:icon.arrow-path variant="mini" class="animate-spin" />
            {{ __('Memuat ringkasan…') }}
        </div>

        <div class="flex flex-col gap-6" wire:loading.class="opacity-50" wire:target="periode">
            {{-- Stat card --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm text-muted">{{ __('Total pendapatan') }}</span>
                        <span class="text-2xl font-bold text-primary" data-test="total-pendapatan">{{ $this->rupiah($ringkasan['totalPendapatan']) }}</span>
                    </div>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><flux:icon.banknotes variant="mini" /></span>
                </div>
                <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm text-muted">{{ __('Jumlah transaksi sah') }}</span>
                        <span class="text-2xl font-bold text-ink" data-test="total-transaksi">{{ number_format($ringkasan['totalTransaksi'], 0, ',', '.') }}</span>
                    </div>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><flux:icon.receipt-percent variant="mini" /></span>
                </div>
                <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm text-muted">{{ __('Cabang terpantau') }}</span>
                        <span class="text-2xl font-bold text-ink" data-test="jumlah-cabang">{{ count($ringkasan['perCabang']) }}</span>
                    </div>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary"><flux:icon.building-storefront variant="mini" /></span>
                </div>
            </div>

            @if ($ringkasan['totalTransaksi'] === 0)
                <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 text-center shadow-card" data-test="dashboard-empty">
                    <flux:heading>{{ __('Belum ada transaksi sah pada periode ini') }}</flux:heading>
                    <flux:text class="text-muted!">{{ __('Data muncul setelah transaksi walk-in, COD, dan canvassing mulai dicatat dan diselesaikan.') }}</flux:text>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Komposisi per kanal --}}
                <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-card lg:col-span-1">
                    <flux:heading>{{ __('Per kanal') }}</flux:heading>
                    <ul class="flex flex-col gap-4">
                        @foreach ($ringkasan['perKanal'] as $item)
                            <li wire:key="kanal-{{ $item['kanal']->value }}" class="flex flex-col gap-1.5" data-test="kanal-{{ $item['kanal']->value }}">
                                <div class="flex items-baseline justify-between gap-2 text-sm">
                                    <span class="font-medium text-ink">{{ $item['kanal']->label() }}</span>
                                    <span class="font-semibold text-ink">{{ $this->rupiah($item['pendapatan']) }}</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-low" aria-hidden="true">
                                    <div class="h-full rounded-full bg-brand" style="width: {{ $item['persen'] }}%"></div>
                                </div>
                                <span class="text-xs text-muted">
                                    {{ __(':count transaksi', ['count' => number_format($item['jumlah'], 0, ',', '.')]) }}
                                    · {{ $item['persen'] }}% {{ __('dari pendapatan') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Rincian per cabang --}}
                <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-card lg:col-span-2">
                    <flux:heading>{{ __('Per cabang') }}</flux:heading>

                    @if (count($ringkasan['perCabang']) === 0)
                        <flux:text class="text-muted!">{{ __('Belum ada data cabang. Tambahkan cabang terlebih dahulu.') }}</flux:text>
                    @else
                        {{-- Desktop: tabel --}}
                        <div class="hidden overflow-x-auto md:block">
                            <table class="w-full text-sm" data-test="tabel-cabang">
                                <thead>
                                    <tr class="bg-surface-low text-left text-xs font-semibold uppercase tracking-wider text-muted">
                                        <th scope="col" class="rounded-l-lg px-3 py-2">{{ __('Cabang') }}</th>
                                        @foreach (\App\Enums\Kanal::cases() as $kanal)
                                            <th scope="col" class="px-3 py-2 text-right">{{ $kanal->label() }}</th>
                                        @endforeach
                                        <th scope="col" class="rounded-r-lg px-3 py-2 text-right">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($ringkasan['perCabang'] as $cabang)
                                        <tr wire:key="cabang-{{ $cabang['id'] }}">
                                            <th scope="row" class="px-3 py-3 text-left font-semibold text-ink">
                                                {{ $cabang['nama'] }}
                                                @unless ($cabang['aktif'])
                                                    <span class="ms-1 rounded-full bg-surface-low px-2 py-0.5 text-xs font-medium text-muted">{{ __('Nonaktif') }}</span>
                                                @endunless
                                            </th>
                                            @foreach ($cabang['kanal'] as $nilai)
                                                <td class="px-3 py-3 text-right">
                                                    <span class="block text-ink">{{ $this->rupiah($nilai['pendapatan']) }}</span>
                                                    <span class="block text-xs text-muted">{{ __(':count transaksi', ['count' => $nilai['jumlah']]) }}</span>
                                                </td>
                                            @endforeach
                                            <td class="px-3 py-3 text-right">
                                                <span class="block font-semibold text-primary">{{ $this->rupiah($cabang['pendapatan']) }}</span>
                                                <span class="block text-xs text-muted">{{ __(':count transaksi', ['count' => $cabang['jumlah']]) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Ponsel: kartu --}}
                        <ul class="flex flex-col gap-3 md:hidden">
                            @foreach ($ringkasan['perCabang'] as $cabang)
                                <li wire:key="cabang-kartu-{{ $cabang['id'] }}" class="flex flex-col gap-2 rounded-xl bg-surface p-4">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <span class="font-semibold text-ink">{{ $cabang['nama'] }}</span>
                                        <span class="font-semibold text-primary">{{ $this->rupiah($cabang['pendapatan']) }}</span>
                                    </div>
                                    <dl class="grid grid-cols-3 gap-2 text-xs">
                                        @foreach (\App\Enums\Kanal::cases() as $kanal)
                                            <div>
                                                <dt class="text-muted">{{ $kanal->label() }}</dt>
                                                <dd class="font-medium text-ink">{{ $cabang['kanal'][$kanal->value]['jumlah'] }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>
