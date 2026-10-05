<?php

use App\Models\Akun;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
        public string $kategoriTab = 'Pengeluaran';

    public function pilihKategori(string $jenis): void
    {
        if (! in_array($jenis, ['Pemasukan', 'Pengeluaran'], true)) {
            return;
        }

        $this->kategoriTab = $jenis;
    }

    #[Computed]
    public function totalSaldo(): float
    {
        return (float) Akun::query()
            ->where('user_id', Auth::id())
            ->get()
            ->sum(fn ($akun) => $akun->saldo);
    }

    #[Computed]
    public function jumlahAkun(): int
    {
        return Akun::query()
            ->where('user_id', Auth::id())
            ->count();
    }

    #[Computed]
    public function pemasukanBulanIni(): float
    {
        return (float) Transaksi::query()
            ->where('user_id', Auth::id())
            ->whereBetween('tanggal', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->whereHas('kategori', function ($query) {
                $query->where('jenis', 'Pemasukan');
            })
            ->sum('jumlah');
    }

    #[Computed]
    public function pengeluaranBulanIni(): float
    {
        return (float) Transaksi::query()
            ->where('user_id', Auth::id())
            ->whereBetween('tanggal', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->whereHas('kategori', function ($query) {
                $query->where('jenis', 'Pengeluaran');
            })
            ->sum('jumlah');
    }

    #[Computed]
    public function cashFlow(): float
    {
        return $this->pemasukanBulanIni - $this->pengeluaranBulanIni;
    }

    #[Computed]
    public function transaksiTerbaru()
    {
        return Transaksi::query()
            ->with(['akun', 'kategori'])
            ->where('user_id', Auth::id())
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function historyPemasukan(): array
    {
        $hasil = [];

        for ($i = 5; $i >= 0; $i--) {

            $bulan = now()->copy()->subMonths($i);

            $total = Transaksi::query()
                ->where('user_id', Auth::id())
                ->whereBetween('tanggal', [
                    $bulan->copy()->startOfMonth()->toDateString(),
                    $bulan->copy()->endOfMonth()->toDateString(),
                ])
                ->whereHas('kategori', function ($query) {
                    $query->where('jenis', 'Pemasukan');
                })
                ->sum('jumlah');

            $hasil[] = [
                'label' => $bulan->translatedFormat('M Y'),
                'total' => (float) $total,
            ];
        }

        return $hasil;
    }

        #[Computed]
    public function kategoriDistribusi()
    {
        return Transaksi::query()
            ->select(
                'kategori.nama_kategori',
                DB::raw('SUM(transaksi.jumlah) as total')
            )
            ->join(
                'kategori',
                'transaksi.kategori_id',
                '=',
                'kategori.id'
            )
            ->where('transaksi.user_id', Auth::id())
            ->where('kategori.user_id', Auth::id())
            ->where('kategori.jenis', $this->kategoriTab)
            ->groupBy(
                'kategori.id',
                'kategori.nama_kategori'
            )
            ->orderByDesc('total')
            ->get();
    }

    #[Computed]
    public function totalKategoriDistribusi(): float
    {
        return (float) $this->kategoriDistribusi->sum('total');
    }

    #[Computed]
    public function grafikMaksimal(): float
    {
        return max(
            $this->pemasukanBulanIni,
            $this->pengeluaranBulanIni,
            1
        );
    }
};
?>

<div class="p-6">

    <div class="mx-auto max-w-7xl">

        {{-- HEADER --}}
        <div class="mb-7">

            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                Dashboard Keuangan
            </h1>

            <p class="mt-1 text-zinc-500 dark:text-zinc-400">
                Ringkasan kondisi keuangan kamu.
            </p>

        </div>


        {{-- CARD UTAMA --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">

            {{-- TOTAL SALDO --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div class="text-sm text-zinc-500">
                        Total Saldo
                    </div>

                    <div class="text-2xl">
                        💰
                    </div>

                </div>

                <div class="mt-3 text-3xl font-bold text-blue-600">
                    Rp {{ number_format($this->totalSaldo, 0, ',', '.') }}
                </div>

                <div class="mt-2 text-xs text-zinc-500">
                    Dari {{ $this->jumlahAkun }} akun
                </div>

            </div>


            {{-- PEMASUKAN --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div class="text-sm text-zinc-500">
                        Pemasukan Bulan Ini
                    </div>

                    <div class="text-2xl">
                        📈
                    </div>

                </div>

                <div class="mt-3 text-3xl font-bold text-green-600">
                    Rp {{ number_format(
                        $this->pemasukanBulanIni,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- PENGELUARAN --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div class="text-sm text-zinc-500">
                        Pengeluaran Bulan Ini
                    </div>

                    <div class="text-2xl">
                        📉
                    </div>

                </div>

                <div class="mt-3 text-3xl font-bold text-red-500">
                    Rp {{ number_format(
                        $this->pengeluaranBulanIni,
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>


            {{-- CASH FLOW --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-900">

                <div class="flex items-center justify-between">

                    <div class="text-sm text-zinc-500">
                        Cash Flow Bulan Ini
                    </div>

                    <div class="text-2xl">
                        ⚖️
                    </div>

                </div>

                <div
                    class="mt-3 text-3xl font-bold
                        {{ $this->cashFlow >= 0
                            ? 'text-green-600'
                            : 'text-red-500' }}"
                >
                    {{ $this->cashFlow >= 0 ? '+' : '-' }}
                    Rp {{ number_format(
                        abs($this->cashFlow),
                        0,
                        ',',
                        '.'
                    ) }}
                </div>

            </div>

        </div>


        {{-- ROW 2 --}}
        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

            {{-- GRAFIK PEMASUKAN VS PENGELUARAN --}}
            <div
                class="rounded-2xl border border-zinc-200 bg-white p-6
                       dark:border-zinc-700 dark:bg-zinc-900 xl:col-span-2"
            >

                <h2 class="text-lg font-bold dark:text-white">
                    Grafik Keuangan Bulan Ini
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    Perbandingan pemasukan dan pengeluaran.
                </p>


                @php
    $pemasukan = (float) $this->pemasukanBulanIni;
    $pengeluaran = (float) $this->pengeluaranBulanIni;
    $totalGrafik = $pemasukan + $pengeluaran;

    if ($totalGrafik > 0) {
        $persenPemasukan = round(($pemasukan / $totalGrafik) * 100, 1);
        $persenPengeluaran = round(($pengeluaran / $totalGrafik) * 100, 1);

        $grafikDonat = "conic-gradient(
            #22c55e 0% {$persenPemasukan}%,
            #ef4444 {$persenPemasukan}% 100%
        )";
    } else {
        $persenPemasukan = 0;
        $persenPengeluaran = 0;
        $grafikDonat = "conic-gradient(#3f3f46 0% 100%)";
    }
@endphp

<div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2 lg:items-center">

    {{-- DONUT --}}
<div class="flex justify-center">

    <div class="relative h-64 w-64">

        <svg
            viewBox="0 0 120 120"
            class="h-full w-full -rotate-90"
        >
            {{-- PENGELUARAN / BACKGROUND --}}
            <circle
                cx="60"
                cy="60"
                r="45"
                fill="none"
                stroke="{{ $totalGrafik > 0 ? '#ef4444' : '#3f3f46' }}"
                stroke-width="18"
            />

            {{-- PEMASUKAN --}}
            <circle
                cx="60"
                cy="60"
                r="45"
                fill="none"
                stroke="#22c55e"
                stroke-width="18"
                pathLength="100"
                stroke-dasharray="{{ $persenPemasukan }} {{ 100 - $persenPemasukan }}"
            />
        </svg>

        {{-- ISI TENGAH --}}
        <div
            class="absolute inset-0 flex flex-col
                   items-center justify-center text-center"
        >
            <span class="text-sm text-zinc-400">
                Total
            </span>

            <span class="mt-1 text-xl font-bold text-white">
                Rp {{ number_format($totalGrafik, 0, ',', '.') }}
            </span>
        </div>

    </div>

</div>


    {{-- DETAIL --}}
    <div class="space-y-4">

        <div
            class="rounded-xl border border-zinc-700
                   bg-zinc-900 p-4"
        >

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">

                    <span class="h-4 w-4 rounded-full bg-green-500"></span>

                    <div>

                        <div class="font-semibold text-green-500">
                            Pemasukan
                        </div>

                        <div class="text-sm text-zinc-400">
                            {{ number_format($persenPemasukan, 1, ',', '.') }}%
                        </div>

                    </div>

                </div>

                <div class="font-bold text-white">
                    Rp {{ number_format($pemasukan, 0, ',', '.') }}
                </div>

            </div>

        </div>


        <div
            class="rounded-xl border border-zinc-700
                   bg-zinc-900 p-4"
        >

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">

                    <span class="h-4 w-4 rounded-full bg-red-500"></span>

                    <div>

                        <div class="font-semibold text-red-500">
                            Pengeluaran
                        </div>

                        <div class="text-sm text-zinc-400">
                            {{ number_format($persenPengeluaran, 1, ',', '.') }}%
                        </div>

                    </div>

                </div>

                <div class="font-bold text-white">
                    Rp {{ number_format($pengeluaran, 0, ',', '.') }}
                </div>

            </div>

        </div>

    </div>

</div>

            </div>


            {{-- INFO AKUN --}}
            <div
                class="rounded-2xl border border-zinc-200 bg-white p-6
                       dark:border-zinc-700 dark:bg-zinc-900"
            >

                <h2 class="text-lg font-bold dark:text-white">
                    Ringkasan Aset
                </h2>

                <div class="mt-6">

                    <div class="text-sm text-zinc-500">
                        Total Aset
                    </div>

                    <div class="mt-1 text-3xl font-bold text-blue-600">
                        Rp {{ number_format(
                            $this->totalSaldo,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>

                </div>

                <div class="mt-6">

                    <div class="text-sm text-zinc-500">
                        Jumlah Akun
                    </div>

                    <div class="mt-1 text-3xl font-bold dark:text-white">
                        {{ $this->jumlahAkun }}
                    </div>

                </div>

                <a
                    href="{{ route('akun') }}"
                    wire:navigate
                    class="mt-7 block rounded-lg bg-blue-600 px-4 py-2.5
                           text-center font-medium text-white
                           hover:bg-blue-700"
                >
                    Lihat Semua Akun
                </a>

            </div>

        </div>


        {{-- HISTORY PEMASUKAN --}}
        <div
            class="mt-6 rounded-2xl border border-zinc-200 bg-white p-6
                   dark:border-zinc-700 dark:bg-zinc-900"
        >

            <h2 class="text-lg font-bold dark:text-white">
                📈 History Pemasukan
            </h2>

            <p class="mt-1 text-sm text-zinc-500">
                Pemasukan 6 bulan terakhir.
            </p>


            <div
                class="mt-6 grid grid-cols-1 gap-4
                       sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6"
            >

                @foreach ($this->historyPemasukan as $item)

                    <div
                        class="rounded-xl border border-zinc-200 p-4
                               dark:border-zinc-700"
                    >

                        <div class="text-sm text-zinc-500">
                            {{ $item['label'] }}
                        </div>

                        <div class="mt-2 font-bold text-green-600">
                            Rp {{ number_format(
                                $item['total'],
                                0,
                                ',',
                                '.'
                            ) }}
                        </div>

                    </div>

                @endforeach

            </div>

        </div>


        {{-- BAWAH --}}
        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">

            {{-- TRANSAKSI TERBARU --}}
            <div
                class="rounded-2xl border border-zinc-200 bg-white
                       dark:border-zinc-700 dark:bg-zinc-900"
            >

                <div
                    class="flex items-center justify-between
                           border-b border-zinc-200 p-6
                           dark:border-zinc-700"
                >

                    <div>

                        <h2 class="text-lg font-bold dark:text-white">
                            Transaksi Terbaru
                        </h2>

                        <p class="text-sm text-zinc-500">
                            5 transaksi terakhir.
                        </p>

                    </div>

                    <a
                        href="{{ route('transaksi') }}"
                        wire:navigate
                        class="text-sm font-medium text-blue-600"
                    >
                        Lihat Semua →
                    </a>

                </div>


                <div>

                    @forelse ($this->transaksiTerbaru as $transaksi)

                        <div
                            class="flex items-center justify-between
                                   border-b border-zinc-200
                                   px-6 py-4 last:border-b-0
                                   dark:border-zinc-700"
                        >

                            <div>

                                <div class="font-semibold dark:text-white">
                                    {{ $transaksi->kategori->nama_kategori }}
                                </div>

                                <div class="mt-1 text-xs text-zinc-500">
                                    {{ $transaksi->akun->nama_akun }}
                                    •
                                    {{ $transaksi->tanggal->format('d/m/Y') }}
                                </div>

                            </div>


                            @if ($transaksi->kategori->jenis === 'Pemasukan')

                                <div class="font-bold text-green-600">
                                    + Rp {{ number_format(
                                        $transaksi->jumlah,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
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

                            @endif

                        </div>

                    @empty

                        <div class="p-8 text-center text-zinc-500">
                            Belum ada transaksi.
                        </div>

                    @endforelse

                </div>

            </div>


            {{-- DISTRIBUSI BERDASARKAN KATEGORI --}}
<div
    class="rounded-2xl border border-zinc-200 bg-white p-6
           dark:border-zinc-700 dark:bg-zinc-900"
>

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <h2 class="text-lg font-bold dark:text-white">
                Distribusi Berdasarkan Kategori
            </h2>

            <p class="mt-1 text-sm text-zinc-500">
                Lihat sumber pemasukan dan penggunaan uang.
            </p>
        </div>


        {{-- TAB --}}
        <div
            class="inline-flex shrink-0 rounded-lg
                   bg-zinc-100 p-1 dark:bg-zinc-800"
        >

            <button
                type="button"
                wire:click="pilihKategori('Pengeluaran')"
                @class([
                    'rounded-md px-3 py-2 text-sm font-medium transition',
                    'bg-red-600 text-white shadow-sm'
                        => $kategoriTab === 'Pengeluaran',
                    'text-zinc-500 hover:text-zinc-900 dark:hover:text-white'
                        => $kategoriTab !== 'Pengeluaran',
                ])
            >
                Pengeluaran
            </button>

            <button
                type="button"
                wire:click="pilihKategori('Pemasukan')"
                @class([
                    'rounded-md px-3 py-2 text-sm font-medium transition',
                    'bg-green-600 text-white shadow-sm'
                        => $kategoriTab === 'Pemasukan',
                    'text-zinc-500 hover:text-zinc-900 dark:hover:text-white'
                        => $kategoriTab !== 'Pemasukan',
                ])
            >
                Pemasukan
            </button>

        </div>

    </div>


    {{-- TOTAL --}}
    <div class="mt-6">

        <div class="text-sm text-zinc-500">
            Total {{ $kategoriTab }}
        </div>

        <div
            @class([
                'mt-1 text-2xl font-bold',
                'text-red-500' => $kategoriTab === 'Pengeluaran',
                'text-green-600' => $kategoriTab === 'Pemasukan',
            ])
        >
            Rp {{ number_format(
                $this->totalKategoriDistribusi,
                0,
                ',',
                '.'
            ) }}
        </div>

    </div>


    {{-- LIST --}}
    <div class="mt-6 space-y-5">

        @forelse ($this->kategoriDistribusi as $item)

            @php
                $persen = $this->totalKategoriDistribusi > 0
                    ? (
                        (float) $item->total
                        / $this->totalKategoriDistribusi
                    ) * 100
                    : 0;
            @endphp

            <div>

                <div class="mb-2 flex justify-between gap-4">

                    <span class="font-medium dark:text-white">
                        {{ $item->nama_kategori }}
                    </span>

                    <span class="text-sm text-zinc-500">
                        Rp {{ number_format(
                            $item->total,
                            0,
                            ',',
                            '.'
                        ) }}
                    </span>

                </div>


                {{-- PROGRESS --}}
                <div
                    class="h-2 overflow-hidden rounded-full
                           bg-zinc-200 dark:bg-zinc-700"
                >

                    <div
                        @class([
                            'h-full rounded-full transition-all duration-300',
                            'bg-red-500'
                                => $kategoriTab === 'Pengeluaran',
                            'bg-green-500'
                                => $kategoriTab === 'Pemasukan',
                        ])
                        @style([
                            'width: '.$persen.'%'
                        ])
                    ></div>

                </div>


                <div
                    class="mt-1 flex justify-between
                           text-xs text-zinc-500"
                >

                    <span>
                        {{ $kategoriTab }}
                    </span>

                    <span>
                        {{ number_format($persen, 1, ',', '.') }}%
                    </span>

                </div>

            </div>

        @empty

            <div
                class="rounded-xl border border-dashed
                       border-zinc-300 p-8 text-center
                       dark:border-zinc-700"
            >

                <div class="text-3xl">
                    {{ $kategoriTab === 'Pengeluaran' ? '💸' : '💰' }}
                </div>

                <div class="mt-3 font-medium dark:text-white">
                    Belum ada data {{ strtolower($kategoriTab) }}
                </div>

                <div class="mt-1 text-sm text-zinc-500">
                    Transaksi {{ strtolower($kategoriTab) }}
                    akan muncul di sini.
                </div>

            </div>

        @endforelse

    </div>

</div>

        </div>

    </div>

</div>