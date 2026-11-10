<?php

use App\Enums\Kanal;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
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

    /**
     * Antrean pesanan COD cabang yang masih berjalan saat ini (tidak bergantung pada periode).
     *
     * @return array{berjalan: int, menungguKurir: int, diantar: int}
     */
    #[Computed]
    public function antrean(): array
    {
        $berjalan = Transaksi::query()
            ->where('cabang_id', $this->cabangId)
            ->where('jenis_kanal', Kanal::Cod)
            ->whereIn('status', [StatusTransaksi::Diproses, StatusTransaksi::Dijemput, StatusTransaksi::Diantar]);

        return [
            'berjalan' => (clone $berjalan)->count(),
            'menungguKurir' => (clone $berjalan)->where('status', StatusTransaksi::Diproses)->whereNull('kurir_id')->count(),
            'diantar' => (clone $berjalan)->where('status', StatusTransaksi::Diantar)->count(),
        ];
    }

    #[Computed]
    public function bisaBukaDaftarPesanan(): bool
    {
        return Auth::user()?->role === Role::AdminCabang;
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
    @php($antrean = $this->antrean)

    {{-- Header halaman Dasbor Cabang --}}
    <div class="flex flex-col gap-5 rounded-2xl bg-white p-6 shadow-card border border-line/60 md:flex-row md:items-center md:justify-between" data-test="dashboard-cabang-card">
        <div class="flex flex-col gap-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-primary">{{ __('Dasbor cabang') }}</span>
            <flux:heading size="xl" level="1" class="text-deep! font-bold text-2xl tracking-tight" data-test="dashboard-cabang-heading">
                {{ __('Ringkasan cabang Anda') }} — {{ $ringkasan['cabangNama'] }}
            </flux:heading>
            <flux:text class="text-muted text-sm">
                {{ __('Ringkasan transaksi sah dan antrean pesanan cabang per') }}
                <span class="font-semibold text-ink" data-test="periode-label">{{ $ringkasan['periodeLabel'] }}</span>
            </flux:text>
        </div>

        <flux:radio.group wire:model.live="periode" variant="segmented" :label="__('Periode')" class="w-full sm:w-auto">
            <flux:radio value="hari_ini" :label="__('Hari ini')" />
            <flux:radio value="bulan_ini" :label="__('Bulan ini')" />
        </flux:radio.group>
    </div>

    <div class="flex items-center gap-2 text-sm text-muted" wire:loading.flex wire:target="periode" role="status">
        <flux:icon.arrow-path variant="mini" class="animate-spin" />
        {{ __('Memuat ringkasan…') }}
    </div>

    <div class="flex flex-col gap-6" wire:loading.class="opacity-50" wire:target="periode">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            {{-- Jumlah transaksi sah --}}
            <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card border border-line/60">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Jumlah transaksi sah') }}</span>
                    <span class="text-2xl font-bold text-ink" data-test="total-transaksi">{{ number_format($ringkasan['totalTransaksi'], 0, ',', '.') }}</span>
                    <span class="text-xs text-muted">{{ __('Walk-in, COD selesai, dan canvassing tervalidasi') }}</span>
                </div>
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary shadow-xs">
                    <flux:icon.receipt-percent variant="mini" class="size-5" />
                </span>
            </div>

            {{-- Total pendapatan --}}
            <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card border border-line/60">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Total pendapatan') }}</span>
                    <span class="text-2xl font-bold text-primary" data-test="total-pendapatan">{{ $this->rupiah($ringkasan['totalPendapatan']) }}</span>
                    <span class="text-xs text-muted">{{ __('Dari transaksi sah pada periode ini') }}</span>
                </div>
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary shadow-xs">
                    <flux:icon.banknotes variant="mini" class="size-5" />
                </span>
            </div>

            {{-- Antrean pesanan COD yang masih berjalan --}}
            <div class="flex items-start justify-between gap-3 rounded-2xl bg-white p-5 shadow-card border border-line/60" data-test="antrean-pesanan">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Pesanan antar berjalan') }}</span>
                    <span class="text-2xl font-bold text-ink" data-test="antrean-berjalan">{{ number_format($antrean['berjalan'], 0, ',', '.') }}</span>
                    <span class="text-xs text-muted" data-test="antrean-rincian">
                        {{ __(':menunggu menunggu kurir · :diantar sedang diantar', ['menunggu' => $antrean['menungguKurir'], 'diantar' => $antrean['diantar']]) }}
                    </span>
                    @if ($this->bisaBukaDaftarPesanan)
                        <a href="{{ route('cabang.pesanan') }}" wire:navigate class="mt-1 text-xs font-semibold text-primary hover:underline" data-test="tautan-daftar-pesanan">
                            {{ __('Buka Daftar Pesanan') }}
                        </a>
                    @endif
                </div>
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary shadow-xs">
                    <flux:icon.truck variant="mini" class="size-5" />
                </span>
            </div>
        </div>

        @if ($ringkasan['totalTransaksi'] === 0)
            <div class="flex flex-col gap-1 rounded-2xl bg-white p-6 text-center shadow-card border border-line/60" data-test="dashboard-cabang-empty">
                <flux:heading>{{ __('Belum ada transaksi sah pada periode ini') }}</flux:heading>
                <flux:text class="text-muted!">{{ __('Data muncul setelah transaksi walk-in, COD, dan canvassing mulai dicatat dan diselesaikan.') }}</flux:text>
            </div>
        @endif

        {{-- Komposisi per kanal --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-card border border-line/60">
            <flux:heading size="sm">{{ __('Per kanal') }}</flux:heading>
            <ul class="grid gap-4 md:grid-cols-3" data-test="daftar-kanal-cabang">
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
</section>
