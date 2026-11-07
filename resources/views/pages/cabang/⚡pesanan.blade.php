<?php

use App\Enums\Kanal;
use App\Enums\Role;
use App\Enums\StatusTransaksi;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\StatusPesananService;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Daftar Pesanan')]
class extends Component {
    use WithPagination;

    /**
     * Tab status yang sedang dibuka.
     */
    #[Url]
    public string $tab = 'aktif';

    public bool $modalKurir = false;

    public ?int $pesananKurirId = null;

    public string $kurirId = '';

    public bool $modalBatal = false;

    public ?int $pesananBatalId = null;

    public string $alasan = '';

    public function mount(): void
    {
        $this->staf();
        $this->normalkanTab();
    }

    public function hydrate(): void
    {
        $this->staf();
    }

    public function updatedTab(): void
    {
        $this->normalkanTab();
        $this->resetPage();
    }

    /**
     * Tab beserta status yang dicakupnya. Tab "aktif" berisi pesanan yang masih berjalan.
     *
     * @return array<string, array{label: string, status: list<StatusTransaksi>}>
     */
    #[Computed]
    public function daftarTab(): array
    {
        return [
            'aktif' => ['label' => __('Aktif'), 'status' => [StatusTransaksi::Diproses, StatusTransaksi::Dijemput, StatusTransaksi::Diantar]],
            'diproses' => ['label' => StatusTransaksi::Diproses->label(), 'status' => [StatusTransaksi::Diproses]],
            'dijemput' => ['label' => StatusTransaksi::Dijemput->label(), 'status' => [StatusTransaksi::Dijemput]],
            'diantar' => ['label' => StatusTransaksi::Diantar->label(), 'status' => [StatusTransaksi::Diantar]],
            'selesai' => ['label' => StatusTransaksi::Selesai->label(), 'status' => [StatusTransaksi::Selesai]],
            'dibatalkan' => ['label' => StatusTransaksi::Dibatalkan->label(), 'status' => [StatusTransaksi::Dibatalkan]],
        ];
    }

    /**
     * Jumlah pesanan cabang per tab.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function jumlahPerTab(): array
    {
        $perStatus = $this->queryPesanan()
            ->toBase()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return collect($this->daftarTab)
            ->map(fn (array $tab): int => collect($tab['status'])
                ->sum(fn (StatusTransaksi $status): int => (int) ($perStatus[$status->value] ?? 0)))
            ->all();
    }

    /**
     * Pesanan pada tab terpilih. Pesanan yang masih berjalan urut dari terlama agar antrean tidak terlewat.
     *
     * @return LengthAwarePaginator<int, Transaksi>
     */
    #[Computed]
    public function pesananList(): LengthAwarePaginator
    {
        $query = $this->queryPesanan()
            ->with(['kurir:id,name', 'riwayatStatus'])
            ->whereIn('status', $this->daftarTab[$this->tab]['status'])
            ->orderBy('id', in_array($this->tab, ['selesai', 'dibatalkan'], true) ? 'desc' : 'asc');

        $hasil = $query->paginate(10);

        // Pesanan terakhir di halaman ini bisa saja baru pindah tab.
        if ($hasil->isEmpty() && $hasil->currentPage() > 1) {
            $this->setPage($hasil->lastPage());
            $hasil = $query->paginate(10);
        }

        return $hasil;
    }

    /**
     * Pilihan kurir: hanya kurir aktif di cabang ini.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function kurirAktif(): Collection
    {
        return User::query()
            ->where('role', Role::Kurir)
            ->where('is_active', true)
            ->where('cabang_id', $this->staf()->cabang_id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function cabangAktif(): bool
    {
        return (bool) $this->staf()->cabang?->is_active;
    }

    #[Computed]
    public function bisaTambahManual(): bool
    {
        return $this->staf()->role === Role::Kasir;
    }

    public function bukaTugaskanKurir(int $pesananId): void
    {
        $pesanan = $this->pesananCabang($pesananId);

        $this->resetValidation();
        $this->pesananKurirId = $pesanan->id;
        $this->kurirId = (string) ($pesanan->kurir_id ?? '');
        $this->modalKurir = true;
    }

    public function simpanKurir(): void
    {
        $this->resetValidation();

        $pesanan = $this->pesananCabang((int) $this->pesananKurirId);
        $kurir = User::query()->find((int) $this->kurirId);

        if ($kurir === null) {
            $this->addError('kurir_id', __('Pilih kurir aktif dari cabang yang sama dengan pesanan.'));

            return;
        }

        $hasil = app(StatusPesananService::class)->tugaskanKurir($pesanan, $kurir, $this->staf());

        $this->reset('modalKurir', 'pesananKurirId', 'kurirId');
        Flux::toast(variant: 'success', text: __('Kurir :kurir ditugaskan ke pesanan #COD-:id.', ['kurir' => $kurir->name, 'id' => $hasil->id]));
    }

    public function ubahStatus(int $pesananId, string $statusBaru): void
    {
        $this->resetValidation();

        $pesanan = $this->pesananCabang($pesananId);
        $status = StatusTransaksi::tryFrom($statusBaru);

        if ($status === null) {
            $this->addError('status', __('Status tujuan tidak dikenal.'));

            return;
        }

        $hasil = app(StatusPesananService::class)->ubahStatus($pesanan, $status, $this->staf());

        Flux::toast(variant: 'success', text: __('Pesanan #COD-:id kini berstatus :status.', ['id' => $hasil->id, 'status' => $hasil->status->label()]));
    }

    public function bukaBatalkan(int $pesananId): void
    {
        $pesanan = $this->pesananCabang($pesananId);

        $this->resetValidation();
        $this->pesananBatalId = $pesanan->id;
        $this->alasan = '';
        $this->modalBatal = true;
    }

    public function simpanBatal(): void
    {
        $this->resetValidation();

        $pesanan = $this->pesananCabang((int) $this->pesananBatalId);

        $hasil = app(StatusPesananService::class)->batalkan($pesanan, $this->staf(), $this->alasan);

        $this->reset('modalBatal', 'pesananBatalId', 'alasan');
        Flux::toast(variant: 'success', text: __('Pesanan #COD-:id dibatalkan.', ['id' => $hasil->id]));
    }

    /**
     * Hanya Kasir dan Admin Cabang; diperiksa ulang di setiap request Livewire.
     */
    private function staf(): User
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User && in_array($user->role, [Role::Kasir, Role::AdminCabang], true), 403);

        return $user;
    }

    /**
     * @return Builder<Transaksi>
     */
    private function queryPesanan(): Builder
    {
        return Transaksi::query()
            ->where('cabang_id', $this->staf()->cabang_id)
            ->where('jenis_kanal', Kanal::Cod);
    }

    /**
     * Pesanan dari ID yang dikirim browser selalu dicari di dalam cabang pengguna.
     */
    private function pesananCabang(int $pesananId): Transaksi
    {
        abort_unless($this->cabangAktif, 403);

        return $this->queryPesanan()->findOrFail($pesananId);
    }

    private function normalkanTab(): void
    {
        if (! array_key_exists($this->tab, $this->daftarTab)) {
            $this->tab = 'aktif';
        }
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    {{-- Header Halaman --}}
    <div class="flex flex-col gap-4 rounded-2xl bg-white p-6 shadow-card md:flex-row md:items-end md:justify-between">
        <div class="flex flex-col gap-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-primary">{{ __('Pesanan & Pengantaran') }}</span>
            <flux:heading size="xl" level="1" class="text-deep!">{{ __('Daftar Pesanan') }}</flux:heading>
            <flux:text class="text-muted!">
                {{ __('Tugaskan kurir, perbarui status, dan batalkan pesanan COD di :cabang.', ['cabang' => auth()->user()?->cabang?->nama ?? '-']) }}
            </flux:text>
        </div>

        @if ($this->bisaTambahManual)
            <div>
                <flux:button variant="primary" icon="plus" :href="route('kasir.pesanan-cod')" wire:navigate data-test="tambah-pesanan-manual">
                    {{ __('Tambah Pesanan Manual') }}
                </flux:button>
            </div>
        @endif
    </div>

    @if (! $this->cabangAktif)
        <div class="flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft p-5 text-danger" role="alert" data-test="alert-cabang-nonaktif">
            <flux:icon.exclamation-triangle variant="mini" class="mt-0.5 size-5 shrink-0" />
            <div class="text-sm">
                <span class="block font-bold">{{ __('Cabang tidak aktif') }}</span>
                <span>{{ __('Daftar pesanan tidak bisa dikelola sampai cabang diaktifkan kembali oleh Super Admin.') }}</span>
            </div>
        </div>
    @else
        {{-- Pesan penolakan dari server, misalnya status sudah berubah oleh staf lain --}}
        @error('status')
            <div class="flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft p-4 text-sm text-danger" role="alert" data-test="error-status">
                <flux:icon.exclamation-circle variant="mini" class="size-5 shrink-0" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        @if (! $modalKurir)
            @error('kurir_id')
                <div class="flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft p-4 text-sm text-danger" role="alert" data-test="error-kurir">
                    <flux:icon.exclamation-circle variant="mini" class="size-5 shrink-0" />
                    <span>{{ $message }}</span>
                </div>
            @enderror
        @endif

        {{-- Tab status --}}
        <div class="flex gap-2 overflow-x-auto rounded-2xl bg-white p-3 shadow-card" role="group" aria-label="{{ __('Filter status pesanan') }}">
            @foreach ($this->daftarTab as $kunci => $item)
                <button
                    type="button"
                    wire:key="tab-{{ $kunci }}"
                    wire:click="$set('tab', '{{ $kunci }}')"
                    aria-pressed="{{ $tab === $kunci ? 'true' : 'false' }}"
                    data-test="tab-{{ $kunci }}"
                    @class([
                        'inline-flex min-h-11 shrink-0 items-center gap-2 rounded-xl px-4 text-sm font-semibold transition',
                        'bg-primary-strong text-white' => $tab === $kunci,
                        'bg-surface-low text-ink hover:bg-primary-soft' => $tab !== $kunci,
                    ])
                >
                    <span>{{ $item['label'] }}</span>
                    <span
                        data-test="jumlah-{{ $kunci }}"
                        @class([
                            'rounded-full px-2 py-0.5 text-xs font-bold',
                            'bg-white/20 text-white' => $tab === $kunci,
                            'bg-white text-ink' => $tab !== $kunci,
                        ])
                    >{{ $this->jumlahPerTab[$kunci] }}</span>
                </button>
            @endforeach
        </div>

        {{-- Daftar pesanan: tabel di desktop, kartu di ponsel --}}
        <div class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-card md:p-6" wire:loading.class="opacity-60">
            @if ($this->pesananList->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center" data-test="pesanan-empty">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-surface-low text-muted">
                        <flux:icon.clipboard-document-list />
                    </span>
                    <flux:heading class="mt-4">{{ __('Belum ada pesanan pada tab ini') }}</flux:heading>
                    <flux:text class="mt-1 text-muted!">{{ __('Pesanan dari pelanggan dan Kasir akan muncul di sini. Muat ulang halaman untuk melihat pesanan terbaru.') }}</flux:text>
                </div>
            @else
                <table class="block w-full text-sm lg:table" data-test="tabel-pesanan">
                    <thead class="hidden lg:table-header-group">
                        <tr class="bg-surface-low text-left text-xs font-semibold uppercase tracking-wider text-muted">
                            <th scope="col" class="rounded-l-lg px-4 py-3">{{ __('Pesanan') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Pemesan & Alamat') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Rincian') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Kurir') }}</th>
                            <th scope="col" class="px-4 py-3">{{ __('Status') }}</th>
                            <th scope="col" class="rounded-r-lg px-4 py-3 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="block divide-y divide-line lg:table-row-group">
                        @foreach ($this->pesananList as $pesanan)
                            @php
                                $berikutnya = $pesanan->status->statusBerikutnyaCod();
                                $masihDiproses = $pesanan->status === \App\Enums\StatusTransaksi::Diproses;
                                $bisaMaju = $berikutnya !== null && (! $masihDiproses || $pesanan->kurir_id !== null);
                                $warnaStatus = match ($pesanan->status) {
                                    \App\Enums\StatusTransaksi::Diproses, \App\Enums\StatusTransaksi::Dijemput => 'bg-info-soft text-info',
                                    \App\Enums\StatusTransaksi::Diantar => 'bg-primary-soft text-primary',
                                    \App\Enums\StatusTransaksi::Selesai => 'bg-success-soft text-success',
                                    default => 'bg-danger-soft text-danger',
                                };
                            @endphp
                            <tr class="block py-4 align-top lg:table-row lg:py-0" wire:key="pesanan-{{ $pesanan->id }}" data-test="pesanan-{{ $pesanan->id }}">
                                <td class="block pb-2 align-top lg:table-cell lg:px-4 lg:py-3.5">
                                    <div class="font-bold text-primary">#COD-{{ $pesanan->id }}</div>
                                    <div class="text-xs text-muted">{{ $pesanan->tanggal->translatedFormat('d M Y') }}, {{ $pesanan->created_at?->format('H:i') }}</div>
                                    <span class="mt-1 inline-flex rounded-full bg-surface-low px-2 py-0.5 text-xs font-medium text-ink">
                                        {{ $pesanan->pelanggan_id !== null ? __('Online') : __('Manual') }}
                                    </span>
                                </td>
                                <td class="block pb-2 align-top lg:table-cell lg:max-w-64 lg:px-4 lg:py-3.5">
                                    <div class="font-semibold text-ink">{{ $pesanan->nama_pemesan }}</div>
                                    <div class="text-xs text-muted">{{ $pesanan->no_hp_pemesan }}</div>
                                    <div class="mt-1 break-words text-ink">{{ $pesanan->alamat_antar }}</div>
                                    @if ($pesanan->status === \App\Enums\StatusTransaksi::Dibatalkan)
                                        <div class="mt-1 break-words text-xs text-danger" data-test="alasan-batal">
                                            {{ __('Alasan batal: :alasan', ['alasan' => $pesanan->riwayatStatus->last()?->catatan ?? '-']) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="block pb-2 align-top lg:table-cell lg:px-4 lg:py-3.5">
                                    <div class="font-semibold text-ink">{{ __(':jumlah galon', ['jumlah' => $pesanan->jumlah_galon]) }}</div>
                                    <div class="font-bold text-primary">Rp{{ number_format($pesanan->total_harga, 0, ',', '.') }}</div>
                                    <div class="text-xs text-muted">{{ $pesanan->metode_bayar->label() }}</div>
                                </td>
                                <td class="block pb-2 align-top lg:table-cell lg:px-4 lg:py-3.5">
                                    @if ($pesanan->kurir)
                                        <span class="font-medium text-ink"><span class="text-muted lg:hidden">{{ __('Kurir') }}: </span>{{ $pesanan->kurir->name }}</span>
                                    @elseif ($masihDiproses)
                                        <span class="inline-flex rounded-full bg-warning-soft px-2.5 py-0.5 text-xs font-semibold text-warning">{{ __('Menunggu kurir') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="block pb-3 align-top lg:table-cell lg:px-4 lg:py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $warnaStatus }}" data-test="status-pesanan">
                                        {{ $pesanan->status->label() }}
                                    </span>
                                </td>
                                <td class="block align-top lg:table-cell lg:px-4 lg:py-3.5">
                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        @if ($bisaMaju)
                                            <flux:button
                                                variant="primary"
                                                wire:click="ubahStatus({{ $pesanan->id }}, '{{ $berikutnya->value }}')"
                                                wire:loading.attr="disabled"
                                                :wire:confirm="$berikutnya === \App\Enums\StatusTransaksi::Selesai ? __('Tandai pesanan #COD-:id selesai? Status selesai tidak bisa diubah lagi.', ['id' => $pesanan->id]) : null"
                                                data-test="maju-{{ $pesanan->id }}"
                                            >
                                                {{ __('Tandai :status', ['status' => $berikutnya->label()]) }}
                                            </flux:button>
                                        @endif

                                        @if ($masihDiproses)
                                            <flux:button
                                                wire:click="bukaTugaskanKurir({{ $pesanan->id }})"
                                                wire:loading.attr="disabled"
                                                data-test="kurir-{{ $pesanan->id }}"
                                            >
                                                {{ $pesanan->kurir_id === null ? __('Pilih Kurir') : __('Ganti Kurir') }}
                                            </flux:button>

                                            <flux:button
                                                variant="ghost"
                                                class="text-danger!"
                                                wire:click="bukaBatalkan({{ $pesanan->id }})"
                                                wire:loading.attr="disabled"
                                                data-test="batal-{{ $pesanan->id }}"
                                            >
                                                {{ __('Batalkan') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($this->pesananList->hasPages())
                    <div class="border-t border-line pt-4">
                        {{ $this->pesananList->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Modal pilih kurir --}}
        <flux:modal wire:model.self="modalKurir" class="w-full md:w-96">
            <form wire:submit="simpanKurir" class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">{{ __('Tugaskan Kurir') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Pesanan #COD-:id. Pilihan hanya kurir aktif di cabang ini.', ['id' => $pesananKurirId]) }}</flux:text>
                </div>

                <flux:error name="status" />

                @if ($this->kurirAktif->isEmpty())
                    <div class="rounded-xl bg-warning-soft p-4 text-sm text-warning" data-test="kurir-kosong">
                        {{ __('Belum ada kurir aktif di cabang ini. Minta Admin Cabang menambahkan atau mengaktifkan akun Kurir.') }}
                    </div>
                @else
                    <flux:field>
                        <flux:label>{{ __('Kurir') }}</flux:label>
                        <flux:select wire:model="kurirId" :placeholder="__('Pilih kurir')" data-test="pilih-kurir">
                            @foreach ($this->kurirAktif as $kurir)
                                <flux:select.option wire:key="kurir-{{ $kurir->id }}" :value="(string) $kurir->id">{{ $kurir->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="kurir_id" />
                    </flux:field>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                    @if ($this->kurirAktif->isNotEmpty())
                        <flux:button type="submit" variant="primary" data-test="simpan-kurir">{{ __('Simpan') }}</flux:button>
                    @endif
                </div>
            </form>
        </flux:modal>

        {{-- Modal batalkan pesanan --}}
        <flux:modal wire:model.self="modalBatal" class="w-full md:w-96">
            <form wire:submit="simpanBatal" class="flex flex-col gap-5">
                <div>
                    <flux:heading size="lg">{{ __('Batalkan Pesanan') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Pesanan #COD-:id akan dibatalkan dan tidak bisa dikembalikan.', ['id' => $pesananBatalId]) }}</flux:text>
                </div>

                <flux:error name="status" />

                <flux:field>
                    <flux:label>{{ __('Alasan pembatalan') }}</flux:label>
                    <flux:textarea wire:model="alasan" rows="3" maxlength="500" data-test="input-alasan" />
                    <flux:error name="alasan" />
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger" data-test="simpan-batal">{{ __('Batalkan Pesanan') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</section>
