<?php

use App\Enums\JenisItem;
use App\Enums\Kanal;
use App\Enums\MetodePembayaran;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Daftar Transaksi Cabang')]
class extends Component {
    use WithPagination;

    /**
     * Filter berdasarkan kanal transaksi (walk_in, cod, canvassing).
     */
    #[Url]
    public string $kanal = '';

    /**
     * Filter berdasarkan status transaksi.
     */
    #[Url]
    public string $status = '';

    /**
     * Filter tanggal awal (YYYY-MM-DD).
     */
    #[Url]
    public string $dari_tanggal = '';

    /**
     * Filter tanggal akhir (YYYY-MM-DD).
     */
    #[Url]
    public string $sampai_tanggal = '';

    public function mount(): void
    {
        $this->staf();
        $this->validasiRentangTanggal();
    }

    public function hydrate(): void
    {
        $this->staf();
    }

    public function updatedKanal(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDariTanggal(): void
    {
        $this->validasiRentangTanggal();
        $this->resetPage();
    }

    public function updatedSampaiTanggal(): void
    {
        $this->validasiRentangTanggal();
        $this->resetPage();
    }

    /**
     * Validasi hubungan tanggal awal dan tanggal akhir.
     */
    private function validasiRentangTanggal(): bool
    {
        $this->resetErrorBag('tanggal');

        if ($this->dari_tanggal !== '' && $this->sampai_tanggal !== '') {
            try {
                $dari = CarbonImmutable::createFromFormat('Y-m-d', $this->dari_tanggal);
                $sampai = CarbonImmutable::createFromFormat('Y-m-d', $this->sampai_tanggal);

                if ($dari->greaterThan($sampai)) {
                    $this->addError('tanggal', __('Tanggal awal ("Dari") tidak boleh lebih besar dari tanggal akhir ("Sampai").'));

                    return false;
                }
            } catch (InvalidFormatException) {
                // Format tidak sah diabaikan secara aman
            }
        }

        return true;
    }

    /**
     * Mengembalikan seluruh filter ke kondisi default dan kembali ke halaman 1.
     */
    public function resetFilter(): void
    {
        $this->reset(['kanal', 'status', 'dari_tanggal', 'sampai_tanggal']);
        $this->resetPage();
        $this->resetErrorBag('tanggal');
    }

    /**
     * Memastikan hanya akun Kasir dan Admin Cabang aktif yang berhak mengakses.
     * Diulang pada setiap siklus request Livewire.
     */
    private function staf(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User && in_array($user->role, [Role::Kasir, Role::AdminCabang], true), 403);

        return $user;
    }

    /**
     * Memeriksa apakah cabang staf terikat dan dalam status aktif.
     */
    #[Computed]
    public function cabangAktif(): bool
    {
        $staf = $this->staf();

        return $staf->cabang_id !== null && (bool) $staf->cabang?->is_active;
    }

    /**
     * Daftar pilihan kanal resmi sesuai enum Kanal.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function daftarKanal(): array
    {
        $hasil = [];
        foreach (Kanal::cases() as $item) {
            $hasil[$item->value] = $item->label();
        }

        return $hasil;
    }

    /**
     * Daftar pilihan status resmi sesuai enum StatusTransaksi.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function daftarStatus(): array
    {
        $hasil = [];
        foreach (StatusTransaksi::cases() as $item) {
            $hasil[$item->value] = $item->label();
        }

        return $hasil;
    }

    /**
     * Menguji apakah ada filter yang sedang aktif diterapkan.
     */
    #[Computed]
    public function adaFilterAktif(): bool
    {
        return $this->kanal !== ''
            || $this->status !== ''
            || $this->dari_tanggal !== ''
            || $this->sampai_tanggal !== '';
    }

    /**
     * Query dasar transaksi cabang dengan isolasi ketat cabang_id staf.
     *
     * @return Builder<Transaksi>
     */
    private function queryTransaksiCabang(): Builder
    {
        $staf = $this->staf();

        // Kunci di server: hanya transaksi dari cabang staf yang bersangkutan
        return Transaksi::query()
            ->where('cabang_id', $staf->cabang_id)
            ->with(['pencatat:id,name', 'pelanggan:id,nama,no_hp', 'kurir:id,name']);
    }

    /**
     * Daftar transaksi terpaginasi dengan filter.
     *
     * @return LengthAwarePaginator<int, Transaksi>
     */
    #[Computed]
    public function transaksiList(): LengthAwarePaginator
    {
        // Jika cabang tidak ada atau tidak aktif, kembalikan paginator kosong
        if (! $this->cabangAktif) {
            return Transaksi::query()->whereRaw('1 = 0')->paginate(15);
        }

        if (! $this->validasiRentangTanggal()) {
            return Transaksi::query()->whereRaw('1 = 0')->paginate(15);
        }

        $query = $this->queryTransaksiCabang();

        // 1. Filter Kanal (hanya nilai enum Kanal yang sah)
        if ($this->kanal !== '') {
            $kanalEnum = Kanal::tryFrom($this->kanal);
            if ($kanalEnum !== null) {
                $query->where('jenis_kanal', $kanalEnum);
            }
        }

        // 2. Filter Status (hanya nilai enum StatusTransaksi yang sah)
        if ($this->status !== '') {
            $statusEnum = StatusTransaksi::tryFrom($this->status);
            if ($statusEnum !== null) {
                $query->where('status', $statusEnum);
            }
        }

        // 3. Filter Rentang Tanggal
        $validDari = null;
        $validSampai = null;

        if ($this->dari_tanggal !== '') {
            try {
                $validDari = CarbonImmutable::createFromFormat('Y-m-d', $this->dari_tanggal);
            } catch (InvalidFormatException) {
                // Abaikan format tanggal tidak sah secara aman
            }
        }

        if ($this->sampai_tanggal !== '') {
            try {
                $validSampai = CarbonImmutable::createFromFormat('Y-m-d', $this->sampai_tanggal);
            } catch (InvalidFormatException) {
                // Abaikan format tanggal tidak sah secara aman
            }
        }

        if ($validDari !== null && $validSampai !== null) {
            // Gunakan scope antaraTanggal yang telah disediakan di model Transaksi
            $query->antaraTanggal($validDari, $validSampai);
        } elseif ($validDari !== null) {
            $query->where('tanggal', '>=', $validDari->startOfDay()->toDateTimeString());
        } elseif ($validSampai !== null) {
            $query->where('tanggal', '<=', $validSampai->endOfDay()->toDateTimeString());
        }

        // 4. Urutan terbaru dulu, dengan tiebreaker id desc
        $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        return $query->paginate(15);
    }

    public function rupiah(int $angka): string
    {
        return 'Rp' . number_format($angka, 0, ',', '.');
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card md:flex-row md:items-end md:justify-between border border-line/60">
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('LOKET CABANG · RIWAYAT TRANSAKSI') }}</span>
            <flux:heading size="xl" level="1" class="text-deep! font-bold text-2xl tracking-tight">
                {{ __('Daftar Transaksi Cabang') }}
            </flux:heading>
            <flux:text class="text-muted! text-sm">
                {{ __('Semua riwayat transaksi di :cabang (Walk-in, COD, dan Canvassing).', ['cabang' => auth()->user()?->cabang?->nama ?? '-']) }}
            </flux:text>
        </div>

        <div class="inline-flex items-center gap-2 rounded-xl bg-surface-low px-3.5 py-2 text-xs font-semibold text-primary self-start md:self-auto border border-line/60">
            <flux:icon.building-storefront variant="mini" class="size-4 shrink-0 text-primary" />
            <span>{{ auth()->user()?->cabang?->nama ?? __('Cabang Belum Ditentukan') }}</span>
        </div>
    </div>

    {{-- Alert Cabang Tidak Aktif --}}
    @if (! $this->cabangAktif)
        <div class="flex items-start gap-3 rounded-2xl bg-danger-soft p-5 border border-danger/20 text-danger" data-test="alert-cabang-nonaktif">
            <flux:icon.exclamation-triangle variant="mini" class="size-5 shrink-0 mt-0.5" />
            <div class="text-sm">
                <span class="font-bold block">{{ __('Peringatan: Cabang Tidak Aktif') }}</span>
                <span>{{ __('Akun Anda belum terikat ke cabang aktif atau cabang sedang dinonaktifkan. Data transaksi tidak dapat ditampilkan.') }}</span>
            </div>
        </div>
    @else
        {{-- Panel Filter --}}
        <div class="rounded-2xl bg-white p-5 shadow-card border border-line/60 space-y-4" data-test="panel-filter">
            <div class="flex items-center justify-between border-b border-line pb-3">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-ink">
                    <flux:icon.funnel variant="mini" class="size-4 text-primary" />
                    <span>{{ __('Filter Transaksi') }}</span>
                </div>

                @if ($this->adaFilterAktif)
                    <button
                        type="button"
                        wire:click="resetFilter"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-danger hover:underline transition"
                        data-test="btn-reset-filter"
                    >
                        <flux:icon.arrow-path variant="mini" class="size-3.5" />
                        <span>{{ __('Reset Filter') }}</span>
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- 1. Filter Kanal --}}
                <flux:field>
                    <flux:label class="text-xs font-semibold text-ink">{{ __('Kanal Penjualan') }}</flux:label>
                    <flux:select wire:model.live="kanal" data-test="filter-kanal">
                        <flux:select.option value="">{{ __('Semua Kanal') }}</flux:select.option>
                        @foreach ($this->daftarKanal as $nilai => $label)
                            <flux:select.option value="{{ $nilai }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                {{-- 2. Filter Status --}}
                <flux:field>
                    <flux:label class="text-xs font-semibold text-ink">{{ __('Status Transaksi') }}</flux:label>
                    <flux:select wire:model.live="status" data-test="filter-status">
                        <flux:select.option value="">{{ __('Semua Status') }}</flux:select.option>
                        @foreach ($this->daftarStatus as $nilai => $label)
                            <flux:select.option value="{{ $nilai }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                {{-- 3. Dari Tanggal --}}
                <flux:field>
                    <flux:label class="text-xs font-semibold text-ink">{{ __('Dari Tanggal') }}</flux:label>
                    <flux:input
                        type="date"
                        wire:model.live="dari_tanggal"
                        data-test="filter-dari-tanggal"
                    />
                </flux:field>

                {{-- 4. Sampai Tanggal --}}
                <flux:field>
                    <flux:label class="text-xs font-semibold text-ink">{{ __('Sampai Tanggal') }}</flux:label>
                    <flux:input
                        type="date"
                        wire:model.live="sampai_tanggal"
                        data-test="filter-sampai-tanggal"
                    />
                </flux:field>
            </div>

            {{-- Pesan Validasi Rentang Tanggal --}}
            @error('tanggal')
                <div class="flex items-center gap-2 rounded-xl bg-danger-soft p-3 text-xs text-danger border border-danger/20" role="alert" data-test="error-tanggal">
                    <flux:icon.exclamation-triangle variant="mini" class="size-4 shrink-0" />
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        {{-- Tabel Daftar Transaksi --}}
        <div class="rounded-2xl bg-white shadow-card border border-line/60 overflow-hidden" data-test="tabel-transaksi-wrapper">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-line bg-surface-low text-muted font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-4">{{ __('Tanggal / ID') }}</th>
                            <th class="py-3.5 px-4">{{ __('Kanal') }}</th>
                            <th class="py-3.5 px-4">{{ __('Item & Kuantitas') }}</th>
                            <th class="py-3.5 px-4">{{ __('Total') }}</th>
                            <th class="py-3.5 px-4">{{ __('Metode') }}</th>
                            <th class="py-3.5 px-4">{{ __('Status') }}</th>
                            <th class="py-3.5 px-4">{{ __('Pencatat / Kurir') }}</th>
                            <th class="py-3.5 px-4">{{ __('Pemesan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/60 text-ink" wire:loading.class="opacity-50">
                        @forelse ($this->transaksiList as $trx)
                            <tr class="hover:bg-surface-low/50 transition-colors" data-test="row-transaksi-{{ $trx->id }}">
                                {{-- Tanggal / ID --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-ink">{{ $trx->tanggal->translatedFormat('d M Y') }}</div>
                                    <div class="text-[10px] text-muted font-mono mt-0.5">#TRX-{{ $trx->id }}</div>
                                </td>

                                {{-- Kanal --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @php
                                        $kanalBadge = match ($trx->jenis_kanal) {
                                            Kanal::WalkIn => 'bg-surface-low text-muted border-line',
                                            Kanal::Cod => 'bg-primary-soft text-primary border-primary/20',
                                            Kanal::Canvassing => 'bg-warning-soft text-warning border-warning/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-bold {{ $kanalBadge }}">
                                        {{ $trx->jenis_kanal->label() }}
                                    </span>
                                </td>

                                {{-- Item & Kuantitas --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-ink whitespace-nowrap">
                                        {{ $trx->jumlah_galon }} {{ __('galon') }}
                                    </div>
                                    <div class="text-[11px] text-muted">
                                        {{ $trx->jenis_item->label() }}
                                    </div>
                                </td>

                                {{-- Total --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-extrabold text-ink">
                                        {{ $this->rupiah($trx->total_harga) }}
                                    </span>
                                </td>

                                {{-- Metode Bayar --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-muted">
                                        @if ($trx->metode_bayar === MetodePembayaran::Tunai)
                                            <flux:icon.banknotes variant="mini" class="size-3.5 text-success" />
                                        @else
                                            <flux:icon.qr-code variant="mini" class="size-3.5 text-primary" />
                                        @endif
                                        <span>{{ $trx->metode_bayar->label() }}</span>
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match ($trx->status) {
                                            StatusTransaksi::Selesai, StatusTransaksi::Tervalidasi => 'bg-success-soft text-success border-success/20',
                                            StatusTransaksi::Diproses => 'bg-primary-soft text-primary border-primary/20',
                                            StatusTransaksi::Dijemput, StatusTransaksi::Diantar => 'bg-primary-soft text-primary-strong border-primary/30',
                                            StatusTransaksi::MenungguVerifikasi => 'bg-warning-soft text-warning border-warning/20',
                                            StatusTransaksi::Dibatalkan, StatusTransaksi::Ditolak => 'bg-danger-soft text-danger border-danger/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-bold {{ $statusClass }}">
                                        {{ $trx->status->label() }}
                                    </span>
                                </td>

                                {{-- Pencatat / Kurir --}}
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if ($trx->pencatat)
                                        <div class="text-ink font-medium">{{ $trx->pencatat->name }}</div>
                                        <div class="text-[10px] text-muted">{{ __('Kasir') }}</div>
                                    @elseif ($trx->kurir)
                                        <div class="text-ink font-medium">{{ $trx->kurir->name }}</div>
                                        <div class="text-[10px] text-muted">{{ __('Kurir') }}</div>
                                    @else
                                        <span class="text-muted italic">{{ __('Online') }}</span>
                                    @endif
                                </td>

                                {{-- Pemesan --}}
                                <td class="py-3.5 px-4 max-w-[200px] truncate">
                                    @if ($trx->pelanggan)
                                        <div class="font-medium text-ink truncate">{{ $trx->pelanggan->nama }}</div>
                                        @if ($trx->pelanggan->no_hp)
                                            <div class="text-[10px] text-muted">{{ $trx->pelanggan->no_hp }}</div>
                                        @endif
                                    @elseif ($trx->nama_pemesan)
                                        <div class="font-medium text-ink truncate">{{ $trx->nama_pemesan }}</div>
                                        @if ($trx->no_hp_pemesan)
                                            <div class="text-[10px] text-muted">{{ $trx->no_hp_pemesan }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">{{ __('-') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="flex size-12 items-center justify-center rounded-2xl bg-surface-low text-muted">
                                            @if ($this->adaFilterAktif)
                                                <flux:icon.magnifying-glass variant="mini" class="size-6" />
                                            @else
                                                <flux:icon.document-text variant="mini" class="size-6" />
                                            @endif
                                        </div>

                                        <div class="text-center">
                                            @if ($this->adaFilterAktif)
                                                <p class="font-bold text-ink text-sm" data-test="empty-filter">
                                                    {{ __('Tidak ada transaksi yang cocok dengan filter.') }}
                                                </p>
                                                <p class="text-xs text-muted mt-1">
                                                    {{ __('Coba ubah atau reset filter untuk melihat data lainnya.') }}
                                                </p>
                                                <button
                                                    type="button"
                                                    wire:click="resetFilter"
                                                    class="mt-3 inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-primary shadow-2xs hover:bg-surface-low"
                                                >
                                                    <flux:icon.arrow-path variant="mini" class="size-3.5" />
                                                    <span>{{ __('Reset Filter') }}</span>
                                                </button>
                                            @else
                                                <p class="font-bold text-ink text-sm" data-test="empty-state">
                                                    {{ __('Belum ada riwayat transaksi di cabang ini.') }}
                                                </p>
                                                <p class="text-xs text-muted mt-1">
                                                    {{ __('Transaksi dari kanal Walk-in, COD, dan Canvassing akan otomatis tercatat di sini.') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($this->transaksiList->hasPages())
                <div class="border-t border-line p-4 bg-surface-low/30" data-test="pagination-wrapper">
                    {{ $this->transaksiList->links() }}
                </div>
            @endif
        </div>
    @endif
</section>
