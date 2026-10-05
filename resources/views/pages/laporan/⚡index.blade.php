<?php

use App\Models\Akun;
use App\Models\Kategori;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $bulan = '';
    public string $akun_id = '';
    public string $jenis = '';
    public string $kategori_id = '';

    public function mount(): void
    {
        $this->bulan = now()->format('Y-m');
    }

    public function updatedJenis(): void
    {
        $this->kategori_id = '';
    }

    #[Computed]
    public function daftarAkun()
    {
        return Akun::query()
            ->where('user_id', Auth::id())
            ->orderBy('nama_akun')
            ->get();
    }

    #[Computed]
    public function daftarKategori()
    {
        return Kategori::query()
            ->where('user_id', Auth::id())
            ->when(
                $this->jenis,
                fn ($query) => $query->where('jenis', $this->jenis)
            )
            ->orderBy('nama_kategori')
            ->get();
    }

    private function periode(): array
    {
        $tanggal = Carbon::createFromFormat(
            'Y-m',
            $this->bulan
        );

        return [
            $tanggal->copy()->startOfMonth(),
            $tanggal->copy()->endOfMonth(),
        ];
    }

    #[Computed]
    public function transaksiLaporan()
    {
        [$awal, $akhir] = $this->periode();

        return Transaksi::query()
            ->with(['akun', 'kategori'])
            ->where('user_id', Auth::id())
            ->whereBetween('tanggal', [
                $awal->toDateString(),
                $akhir->toDateString(),
            ])
            ->when(
                $this->akun_id,
                fn ($query) =>
                    $query->where('akun_id', $this->akun_id)
            )
            ->when(
                $this->jenis,
                fn ($query) =>
                    $query->whereHas(
                        'kategori',
                        fn ($q) =>
                            $q->where('jenis', $this->jenis)
                    )
            )
            ->when(
                $this->kategori_id,
                fn ($query) =>
                    $query->where(
                        'kategori_id',
                        $this->kategori_id
                    )
            )
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();
    }

    #[Computed]
    public function totalPemasukan(): float
    {
        return (float) $this->transaksiLaporan
            ->filter(
                fn ($transaksi) =>
                    $transaksi->kategori->jenis === 'Pemasukan'
            )
            ->sum('jumlah');
    }

    #[Computed]
    public function totalPengeluaran(): float
    {
        return (float) $this->transaksiLaporan
            ->filter(
                fn ($transaksi) =>
                    $transaksi->kategori->jenis === 'Pengeluaran'
            )
            ->sum('jumlah');
    }

    #[Computed]
    public function selisih(): float
    {
        return $this->totalPemasukan
            - $this->totalPengeluaran;
    }

    #[Computed]
    public function saldoAwalPeriode(): float
    {
        [$awal] = $this->periode();

        $akun = Akun::query()
            ->where('user_id', Auth::id())
            ->when(
                $this->akun_id,
                fn ($query) =>
                    $query->where('id', $this->akun_id)
            )
            ->get();

        $saldo = (float) $akun->sum('saldo_awal');

        $akunIds = $akun->pluck('id');

        if ($akunIds->isEmpty()) {
            return 0;
        }

        $transaksiSebelumnya = Transaksi::query()
            ->with('kategori')
            ->where('user_id', Auth::id())
            ->whereIn('akun_id', $akunIds)
            ->whereDate(
                'tanggal',
                '<',
                $awal->toDateString()
            )
            ->get();

        foreach ($transaksiSebelumnya as $transaksi) {

            if ($transaksi->kategori->jenis === 'Pemasukan') {
                $saldo += $transaksi->jumlah;
            } else {
                $saldo -= $transaksi->jumlah;
            }
        }

        return $saldo;
    }

    #[Computed]
    public function saldoAkhirPeriode(): float
    {
        [$awal, $akhir] = $this->periode();

        $query = Transaksi::query()
            ->with('kategori')
            ->where('user_id', Auth::id())
            ->whereBetween('tanggal', [
                $awal->toDateString(),
                $akhir->toDateString(),
            ]);

        if ($this->akun_id) {
            $query->where('akun_id', $this->akun_id);
        }

        $saldo = $this->saldoAwalPeriode;

        foreach ($query->get() as $transaksi) {

            if ($transaksi->kategori->jenis === 'Pemasukan') {
                $saldo += $transaksi->jumlah;
            } else {
                $saldo -= $transaksi->jumlah;
            }
        }

        return $saldo;
    }
};
?>

<div class="p-6">

    <div class="mx-auto max-w-7xl">

        {{-- HEADER --}}
        <div class="mb-6">

            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                Laporan Keuangan
            </h1>

            <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                Lihat ringkasan dan mutasi keuangan berdasarkan periode.
            </p>

        </div>


        {{-- FILTER --}}
        <div
            class="mb-6 rounded-2xl border border-zinc-200
                   bg-white p-5 dark:border-zinc-700
                   dark:bg-zinc-900"
        >

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                {{-- BULAN --}}
                <div class="min-w-0">

                    <label class="mb-2 block text-sm font-medium dark:text-white">
                        Periode
                    </label>

                    <select
        wire:model.live="bulan"
        class="w-full min-w-0 rounded-lg border border-zinc-300
               bg-white px-4 py-2.5
               dark:border-zinc-700 dark:bg-zinc-800"
    >
        @for ($i = -12; $i <= 12; $i++)
            @php
                $periode = now()->copy()->addMonths($i);
            @endphp

            <option value="{{ $periode->format('Y-m') }}">
                {{ $periode->translatedFormat('F Y') }}
            </option>
        @endfor
    </select>

                </div>


                {{-- AKUN --}}
                <div>

                    <label class="mb-2 block text-sm font-medium dark:text-white">
                        Akun
                    </label>

                    <select
                        wire:model.live="akun_id"
                        class="w-full rounded-lg border border-zinc-300
                               bg-white px-4 py-2.5
                               dark:border-zinc-700 dark:bg-zinc-800"
                    >

                        <option value="">
                            Semua Akun
                        </option>

                        @foreach ($this->daftarAkun as $akun)

                            <option value="{{ $akun->id }}">
                                {{ $akun->nama_akun }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- JENIS --}}
                <div>

                    <label class="mb-2 block text-sm font-medium dark:text-white">
                        Jenis
                    </label>

                    <select
                        wire:model.live="jenis"
                        class="w-full rounded-lg border border-zinc-300
                               bg-white px-4 py-2.5
                               dark:border-zinc-700 dark:bg-zinc-800"
                    >

                        <option value="">
                            Semua Jenis
                        </option>

                        <option value="Pemasukan">
                            Pemasukan
                        </option>

                        <option value="Pengeluaran">
                            Pengeluaran
                        </option>

                    </select>

                </div>


                {{-- KATEGORI --}}
                <div>

                    <label class="mb-2 block text-sm font-medium dark:text-white">
                        Kategori
                    </label>

                    <select
                        wire:model.live="kategori_id"
                        class="w-full rounded-lg border border-zinc-300
                               bg-white px-4 py-2.5
                               dark:border-zinc-700 dark:bg-zinc-800"
                    >

                        <option value="">
                            Semua Kategori
                        </option>

                        @foreach ($this->daftarKategori as $kategori)

                            <option value="{{ $kategori->id }}">
                                {{ $kategori->nama_kategori }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

        </div>


        {{-- RINGKASAN --}}
        <div
            class="mb-6 grid grid-cols-1 gap-5
                   md:grid-cols-2 xl:grid-cols-4"
        >

            {{-- SALDO AWAL --}}
            <div
                class="rounded-2xl border border-zinc-200
                       bg-white p-5 dark:border-zinc-700
                       dark:bg-zinc-900"
            >

                <div class="text-sm text-zinc-500">
                    Saldo Awal Periode
                </div>

                <div class="mt-2 text-2xl font-bold text-blue-600">
                    Rp {{ number_format(
                        $this->saldoAwalPeriode,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- PEMASUKAN --}}
            <div
                class="rounded-2xl border border-zinc-200
                       bg-white p-5 dark:border-zinc-700
                       dark:bg-zinc-900"
            >

                <div class="text-sm text-zinc-500">
                    Pemasukan
                </div>

                <div class="mt-2 text-2xl font-bold text-green-600">
                    + Rp {{ number_format(
                        $this->totalPemasukan,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- PENGELUARAN --}}
            <div
                class="rounded-2xl border border-zinc-200
                       bg-white p-5 dark:border-zinc-700
                       dark:bg-zinc-900"
            >

                <div class="text-sm text-zinc-500">
                    Pengeluaran
                </div>

                <div class="mt-2 text-2xl font-bold text-red-500">
                    - Rp {{ number_format(
                        $this->totalPengeluaran,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- SALDO AKHIR --}}
            <div
                class="rounded-2xl border border-zinc-200
                       bg-white p-5 dark:border-zinc-700
                       dark:bg-zinc-900"
            >

                <div class="text-sm text-zinc-500">
                    Saldo Akhir Periode
                </div>

                <div
                    class="mt-2 text-2xl font-bold
                        {{ $this->saldoAkhirPeriode >= 0
                            ? 'text-green-600'
                            : 'text-red-500' }}"
                >
                    Rp {{ number_format(
                        $this->saldoAkhirPeriode,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>

        </div>


        {{-- CASH FLOW --}}
        <div
            class="mb-6 flex flex-col gap-3 rounded-2xl
                   border border-zinc-200 bg-white p-5
                   sm:flex-row sm:items-center
                   sm:justify-between
                   dark:border-zinc-700 dark:bg-zinc-900"
        >

            <div>

                <div class="text-sm text-zinc-500">
                    Cash Flow / Selisih
                </div>

                <div
                    class="mt-1 text-2xl font-bold
                        {{ $this->selisih >= 0
                            ? 'text-green-600'
                            : 'text-red-500' }}"
                >
                    {{ $this->selisih >= 0 ? '+' : '-' }}

                    Rp {{ number_format(
                        abs($this->selisih),
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>

            <div class="text-sm text-zinc-500">
                {{ $this->transaksiLaporan->count() }}
                transaksi ditemukan
            </div>

        </div>


        {{-- TABEL LAPORAN --}}
        <div
            class="overflow-hidden rounded-2xl
                   border border-zinc-200
                   bg-white dark:border-zinc-700
                   dark:bg-zinc-900"
        >

            <div class="border-b border-zinc-200 p-5 dark:border-zinc-700">

                <h2 class="text-lg font-bold dark:text-white">
                    Mutasi Transaksi
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    Detail pemasukan dan pengeluaran pada periode terpilih.
                </p>

            </div>


            {{-- MOBILE MUTASI --}}
<div class="space-y-3 p-4 md:hidden">

    @forelse ($this->transaksiLaporan as $transaksi)

        <div
            wire:key="mobile-laporan-{{ $transaksi->id }}"
            class="rounded-xl border border-zinc-200
                   bg-white p-4
                   dark:border-zinc-700 dark:bg-zinc-900"
        >

            <div class="flex items-start justify-between gap-3">

                <div class="min-w-0">

                    <div class="text-xs text-zinc-500">
                        {{ $transaksi->tanggal->format('d/m/Y') }}
                    </div>

                    <div class="mt-1 font-semibold text-zinc-900 dark:text-white">
                        {{ $transaksi->kategori->nama_kategori }}
                    </div>

                    <div class="mt-1 text-xs text-zinc-500">
                        {{ $transaksi->akun->nama_akun }}
                        •
                        {{ $transaksi->akun->jenis_akun }}
                    </div>

                </div>


                <div class="shrink-0 text-right">

                    @if ($transaksi->kategori->jenis === 'Pemasukan')

                        <div class="font-bold text-green-600">
                            + Rp {{ number_format(
                                $transaksi->jumlah,
                                0,
                                ',',
                                '.'
                            ) }}
                        </div>

                        <div class="mt-1 text-xs text-green-600">
                            Pemasukan
                        </div>

                    @else

                        <div class="font-bold text-red-500">
                            - Rp {{ number_format(
                                $transaksi->jumlah,
                                0,
                                ',',
                                '.'
                            ) }}
                        </div>

                        <div class="mt-1 text-xs text-red-500">
                            Pengeluaran
                        </div>

                    @endif

                </div>

            </div>


            @if ($transaksi->keterangan)

                <div class="mt-3 rounded-lg bg-zinc-100
                            px-3 py-2 text-sm text-zinc-600
                            dark:bg-zinc-800 dark:text-zinc-400">

                    {{ $transaksi->keterangan }}

                </div>

            @endif

        </div>

    @empty

        <div class="py-10 text-center text-zinc-500">

            <div class="text-4xl">
                🧾
            </div>

            <div class="mt-3 font-semibold text-zinc-900 dark:text-white">
                Tidak ada transaksi
            </div>

            <div class="mt-1 text-sm">
                Belum ada transaksi pada periode dan filter ini.
            </div>

        </div>

    @endforelse

</div>

            <div class="hidden overflow-x-auto md:block">

                <table class="w-full">

                    <thead class="bg-zinc-100 dark:bg-zinc-800">

                        <tr>

                            <th class="px-5 py-4 text-left">
                                Tanggal
                            </th>

                            <th class="px-5 py-4 text-left">
                                Akun
                            </th>

                            <th class="px-5 py-4 text-left">
                                Kategori
                            </th>

                            <th class="px-5 py-4 text-left">
                                Keterangan
                            </th>

                            <th class="px-5 py-4 text-right">
                                Masuk
                            </th>

                            <th class="px-5 py-4 text-right">
                                Keluar
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($this->transaksiLaporan as $transaksi)

                            <tr
                                wire:key="laporan-{{ $transaksi->id }}"
                                class="border-t border-zinc-200
                                       dark:border-zinc-700"
                            >

                                <td class="px-5 py-4">
                                    {{ $transaksi->tanggal->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-4">

                                    <div class="font-medium dark:text-white">
                                        {{ $transaksi->akun->nama_akun }}
                                    </div>

                                    <div class="text-xs text-zinc-500">
                                        {{ $transaksi->akun->jenis_akun }}
                                    </div>

                                </td>

                                <td class="px-5 py-4">

                                    @if ($transaksi->kategori->jenis === 'Pemasukan')

                                        <span
                                            class="rounded-full bg-green-100
                                                   px-3 py-1 text-xs
                                                   text-green-700
                                                   dark:bg-green-900/30
                                                   dark:text-green-300"
                                        >
                                            ↑ {{ $transaksi->kategori->nama_kategori }}
                                        </span>

                                    @else

                                        <span
                                            class="rounded-full bg-red-100
                                                   px-3 py-1 text-xs
                                                   text-red-700
                                                   dark:bg-red-900/30
                                                   dark:text-red-300"
                                        >
                                            ↓ {{ $transaksi->kategori->nama_kategori }}
                                        </span>

                                    @endif

                                </td>

                                <td class="px-5 py-4 text-zinc-500">
                                    {{ $transaksi->keterangan ?: '-' }}
                                </td>

                                <td class="px-5 py-4 text-right">

                                    @if ($transaksi->kategori->jenis === 'Pemasukan')

                                        <span class="font-bold text-green-600">
                                            Rp {{ number_format(
                                                $transaksi->jumlah,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @else

                                        -
                                    @endif

                                </td>

                                <td class="px-5 py-4 text-right">

                                    @if ($transaksi->kategori->jenis === 'Pengeluaran')

                                        <span class="font-bold text-red-500">
                                            Rp {{ number_format(
                                                $transaksi->jumlah,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @else

                                        -
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-16
                                           text-center text-zinc-500"
                                >
                                    Tidak ada transaksi pada filter ini.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        @if ($this->jenis || $this->kategori_id)

            <div
                class="mt-4 rounded-lg bg-amber-100
                       px-4 py-3 text-sm text-amber-800
                       dark:bg-amber-900/30
                       dark:text-amber-300"
            >
                Catatan: Saldo awal dan saldo akhir mengikuti
                periode serta akun. Filter jenis/kategori hanya
                memengaruhi ringkasan transaksi dan tabel.
            </div>

        @endif

    </div>

</div>