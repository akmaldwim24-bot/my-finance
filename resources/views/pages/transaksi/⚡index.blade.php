<?php

use App\Models\Akun;
use App\Models\Kategori;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $tanggal = '';
    public string $jenis = 'Pengeluaran';

    public string $akun_id = '';
    public string $kategori_id = '';
    public string $jumlah = '';
    public string $keterangan = '';

    public ?int $editingId = null;
    public bool $showModal = false;

    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d');
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
            ->where('jenis', $this->jenis)
            ->orderBy('nama_kategori')
            ->get();
    }

    #[Computed]
    public function daftarTransaksi()
    {
        return Transaksi::query()
            ->with(['akun', 'kategori'])
            ->where('user_id', Auth::id())
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
    }

    public function updatedJenis(): void
    {
        $this->kategori_id = '';

        unset($this->daftarKategori);
    }

    public function bukaTambah(string $jenis = 'Pengeluaran'): void
    {
        $this->resetForm();

        $this->jenis = $jenis;
        $this->showModal = true;

        unset($this->daftarKategori);
    }

    public function edit(int $id): void
    {
        $transaksi = Transaksi::query()
            ->with('kategori')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->editingId = $transaksi->id;

        $this->tanggal = $transaksi->tanggal->format('Y-m-d');
        $this->akun_id = (string) $transaksi->akun_id;
        $this->kategori_id = (string) $transaksi->kategori_id;
        $this->jumlah = (string) $transaksi->jumlah;
        $this->keterangan = $transaksi->keterangan ?? '';

        $this->jenis = $transaksi->kategori->jenis;

        unset($this->daftarKategori);

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'tanggal' => [
                'required',
                'date',
            ],

            'akun_id' => [
                'required',
                Rule::exists('akun', 'id')
                    ->where(fn ($query) =>
                        $query->where('user_id', Auth::id())
                    ),
            ],

            'kategori_id' => [
                'required',
                Rule::exists('kategori', 'id')
                    ->where(fn ($query) =>
                        $query
                            ->where('user_id', Auth::id())
                            ->where('jenis', $this->jenis)
                    ),
            ],

            'jumlah' => [
                'required',
                'numeric',
                'min:1',
            ],

            'keterangan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'akun_id.required' => 'Akun wajib dipilih.',
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.min' => 'Jumlah minimal Rp 1.',
        ]);

        if ($this->editingId) {

            $transaksi = Transaksi::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingId);

            $transaksi->update([
                'tanggal' => $data['tanggal'],
                'akun_id' => $data['akun_id'],
                'kategori_id' => $data['kategori_id'],
                'jumlah' => $data['jumlah'],
                'keterangan' => $data['keterangan'] ?: null,
            ]);

            session()->flash(
                'success',
                'Transaksi berhasil diperbarui.'
            );

        } else {

            Transaksi::create([
                'user_id' => Auth::id(),
                'tanggal' => $data['tanggal'],
                'akun_id' => $data['akun_id'],
                'kategori_id' => $data['kategori_id'],
                'jumlah' => $data['jumlah'],
                'keterangan' => $data['keterangan'] ?: null,
            ]);

            session()->flash(
                'success',
                'Transaksi berhasil ditambahkan.'
            );
        }

        $this->showModal = false;

        $this->resetForm();

        unset($this->daftarTransaksi);
    }

    public function hapus(int $id): void
    {
        $transaksi = Transaksi::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $transaksi->delete();

        unset($this->daftarTransaksi);

        session()->flash(
            'success',
            'Transaksi berhasil dihapus.'
        );
    }

    public function tutupModal(): void
    {
        $this->showModal = false;

        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->resetValidation();

        $this->editingId = null;

        $this->tanggal = now()->format('Y-m-d');
        $this->jenis = 'Pengeluaran';

        $this->akun_id = '';
        $this->kategori_id = '';
        $this->jumlah = '';
        $this->keterangan = '';

        unset($this->daftarKategori);
    }
};
?>

<div class="p-6">

    <div class="mx-auto max-w-7xl">

        {{-- HEADER --}}
        <div
            class="mb-6 flex flex-col gap-4
                   lg:flex-row lg:items-center
                   lg:justify-between"
        >

            <div>
                <h1
                    class="text-2xl font-bold
                           text-zinc-900 dark:text-white"
                >
                    Transaksi
                </h1>

                <p
                    class="mt-1 text-sm text-zinc-500
                           dark:text-zinc-400"
                >
                    Catat pemasukan dan pengeluaran.
                </p>
            </div>

            <div class="flex gap-2">

                <button
                    wire:click="bukaTambah('Pemasukan')"
                    class="rounded-lg bg-green-600
                           px-4 py-2 font-medium
                           text-white hover:bg-green-700"
                >
                    + Pemasukan
                </button>

                <button
                    wire:click="bukaTambah('Pengeluaran')"
                    class="rounded-lg bg-red-600
                           px-4 py-2 font-medium
                           text-white hover:bg-red-700"
                >
                    + Pengeluaran
                </button>

            </div>

        </div>


        {{-- NOTIFIKASI --}}
        @if (session('success'))

            <div
                class="mb-5 rounded-lg bg-green-100
                       px-4 py-3 text-green-800
                       dark:bg-green-900/30
                       dark:text-green-300"
            >
                {{ session('success') }}
            </div>

        @endif

        

        {{-- TABEL --}}
        {{-- MOBILE LIST --}}
<div class="space-y-3 p-3 md:hidden">

    @forelse ($this->daftarTransaksi as $transaksi)

        <div
            wire:key="mobile-transaksi-{{ $transaksi->id }}"
            class="rounded-xl border border-zinc-200 bg-white p-4
                   dark:border-zinc-700 dark:bg-zinc-900"
        >

            {{-- BARIS ATAS --}}
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


            {{-- KETERANGAN --}}
            @if ($transaksi->keterangan)

                <div class="mt-3 rounded-lg bg-zinc-100 px-3 py-2
                            text-sm text-zinc-600
                            dark:bg-zinc-800 dark:text-zinc-400">

                    {{ $transaksi->keterangan }}

                </div>

            @endif


            {{-- AKSI --}}
            <div class="mt-4 flex justify-end gap-2">

                <button
                    wire:click="edit({{ $transaksi->id }})"
                    class="rounded-lg bg-amber-500 px-3 py-2
                           text-sm text-white hover:bg-amber-600"
                >
                    ✏ Edit
                </button>

                <button
                    wire:click="hapus({{ $transaksi->id }})"
                    wire:confirm="Yakin ingin menghapus transaksi ini?"
                    class="rounded-lg bg-red-600 px-3 py-2
                           text-sm text-white hover:bg-red-700"
                >
                    🗑 Hapus
                </button>

            </div>

        </div>

    @empty

        <div class="py-12 text-center text-zinc-500">

            <div class="text-4xl">
                🧾
            </div>

            <div class="mt-3 font-semibold text-zinc-900 dark:text-white">
                Belum ada transaksi
            </div>

            <div class="mt-1 text-sm">
                Tambahkan pemasukan atau pengeluaran pertama.
            </div>

        </div>

    @endforelse

</div>


        <div
            class="overflow-hidden rounded-2xl
                   border border-zinc-200
                   bg-white
                   dark:border-zinc-700
                   dark:bg-zinc-900"
        >

        

            <div class="hidden overflow-x-auto md:block">

                <table class="w-full">

                    <thead
                        class="bg-zinc-100
                               dark:bg-zinc-800"
                    >

                        <tr>

                            <th
                                class="px-5 py-4 text-left
                                       text-sm font-semibold"
                            >
                                Tanggal
                            </th>

                            <th
                                class="px-5 py-4 text-left
                                       text-sm font-semibold"
                            >
                                Akun
                            </th>

                            <th
                                class="px-5 py-4 text-left
                                       text-sm font-semibold"
                            >
                                Kategori
                            </th>

                            <th
                                class="px-5 py-4 text-left
                                       text-sm font-semibold"
                            >
                                Keterangan
                            </th>

                            <th
                                class="px-5 py-4 text-right
                                       text-sm font-semibold"
                            >
                                Jumlah
                            </th>

                            <th
                                class="px-5 py-4 text-center
                                       text-sm font-semibold"
                            >
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse ($this->daftarTransaksi as $transaksi)

                            <tr
                                wire:key="transaksi-{{ $transaksi->id }}"
                                class="border-t
                                       border-zinc-200
                                       dark:border-zinc-700"
                            >

                                <td class="px-5 py-4">

                                    {{ $transaksi->tanggal->format('d/m/Y') }}

                                </td>


                                <td class="px-5 py-4">

                                    <div class="font-medium">
                                        {{ $transaksi->akun->nama_akun }}
                                    </div>

                                    <div
                                        class="text-xs text-zinc-500"
                                    >
                                        {{ $transaksi->akun->jenis_akun }}
                                    </div>

                                </td>


                                <td class="px-5 py-4">

                                    @if ($transaksi->kategori->jenis === 'Pemasukan')

                                        <span
                                            class="rounded-full
                                                   bg-green-100
                                                   px-3 py-1
                                                   text-xs font-medium
                                                   text-green-700
                                                   dark:bg-green-900/30
                                                   dark:text-green-300"
                                        >
                                            ↑ {{ $transaksi->kategori->nama_kategori }}
                                        </span>

                                    @else

                                        <span
                                            class="rounded-full
                                                   bg-red-100
                                                   px-3 py-1
                                                   text-xs font-medium
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

                                        <span
                                            class="font-bold
                                                   text-green-600"
                                        >
                                            + Rp {{ number_format(
                                                $transaksi->jumlah,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @else

                                        <span
                                            class="font-bold
                                                   text-red-500"
                                        >
                                            - Rp {{ number_format(
                                                $transaksi->jumlah,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </span>

                                    @endif

                                </td>


                                <td class="px-5 py-4">

                                    <div
                                        class="flex justify-center gap-2"
                                    >

                                        <button
                                            wire:click="edit({{ $transaksi->id }})"
                                            class="rounded-lg
                                                   bg-amber-500
                                                   px-3 py-2
                                                   text-sm text-white
                                                   hover:bg-amber-600"
                                        >
                                            ✏
                                        </button>

                                        <button
                                            wire:click="hapus({{ $transaksi->id }})"
                                            wire:confirm="Yakin ingin menghapus transaksi ini?"
                                            class="rounded-lg
                                                   bg-red-600
                                                   px-3 py-2
                                                   text-sm text-white
                                                   hover:bg-red-700"
                                        >
                                            🗑
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-6 py-16
                                           text-center
                                           text-zinc-500"
                                >

                                    <div class="text-5xl">
                                        🧾
                                    </div>

                                    <div
                                        class="mt-4 font-semibold
                                               text-zinc-900
                                               dark:text-white"
                                    >
                                        Belum ada transaksi
                                    </div>

                                    <div class="mt-1 text-sm">
                                        Tambahkan pemasukan atau
                                        pengeluaran pertama.
                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- MODAL --}}
        @if ($showModal)

            <div
                class="fixed inset-0 z-50
                       flex items-center justify-center
                       overflow-y-auto bg-black/60 p-3
                       sm:items-center sm:p-4"
            >

                <div
                    class="my-3 flex max-h-[calc(100dvh-1.5rem)]
                    w-full max-w-xl flex-col overflow-hidden
                    rounded-2xl bg-white shadow-xl
                    dark:bg-zinc-900 sm:my-0"
                >

                    <div
                        class="flex items-center
                               justify-between
                               border-b
                               border-zinc-200
                               px-6 py-4
                               dark:border-zinc-700"
                    >

                        <div>

                            <h2
                                class="text-lg font-bold
                                       dark:text-white"
                            >
                                {{ $editingId ? 'Edit Transaksi' : 'Tambah Transaksi' }}
                            </h2>

                            <p
                                class="text-sm
                                       {{ $jenis === 'Pemasukan'
                                           ? 'text-green-600'
                                           : 'text-red-500' }}"
                            >
                                {{ $jenis }}
                            </p>

                        </div>

                        <button
                            wire:click="tutupModal"
                            class="text-2xl
                                   text-zinc-500
                                   hover:text-zinc-900
                                   dark:hover:text-white"
                        >
                            ×
                        </button>

                    </div>


                    <form wire:submit="simpan" class="flex min-h-0 flex-1 flex-col">

                        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5 sm:p-6">

                            {{-- JENIS --}}
                            <div>

                                <label
                                    class="mb-2 block
                                           text-sm font-medium
                                           dark:text-white"
                                >
                                    Jenis Transaksi
                                </label>

                                <select
                                    wire:model.live="jenis"
                                    class="w-full rounded-lg
                                           border border-zinc-300
                                           bg-white px-4 py-2.5
                                           dark:border-zinc-700
                                           dark:bg-zinc-800"
                                >

                                    <option value="Pemasukan">
                                        Pemasukan
                                    </option>

                                    <option value="Pengeluaran">
                                        Pengeluaran
                                    </option>

                                </select>

                            </div>


                            {{-- TANGGAL --}}
                            <div>

                                <label
                                    class="mb-2 block
                                           text-sm font-medium
                                           dark:text-white"
                                >
                                    Tanggal
                                </label>

                                <input
                                    wire:model="tanggal"
                                    type="date"
                                    class="w-full rounded-lg
                                           border border-zinc-300
                                           bg-white px-4 py-2.5
                                           dark:border-zinc-700
                                           dark:bg-zinc-800"
                                >

                                @error('tanggal')

                                    <div
                                        class="mt-1 text-sm
                                               text-red-500"
                                    >
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- AKUN --}}
                            <div>

                                <label
                                    class="mb-2 block
                                           text-sm font-medium
                                           dark:text-white"
                                >
                                    Akun
                                </label>

                                <select
                                    wire:model="akun_id"
                                    class="w-full rounded-lg
                                           border border-zinc-300
                                           bg-white px-4 py-2.5
                                           dark:border-zinc-700
                                           dark:bg-zinc-800"
                                >

                                    <option value="">
                                        -- Pilih Akun --
                                    </option>

                                    @foreach ($this->daftarAkun as $akun)

                                        <option value="{{ $akun->id }}">
                                            {{ $akun->nama_akun }}
                                            — Rp {{ number_format(
                                                $akun->saldo,
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('akun_id')

                                    <div
                                        class="mt-1 text-sm
                                               text-red-500"
                                    >
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- KATEGORI --}}
                            <div>

                                <label
                                    class="mb-2 block
                                           text-sm font-medium
                                           dark:text-white"
                                >
                                    Kategori
                                </label>

                                <select
                                    wire:model="kategori_id"
                                    class="w-full rounded-lg
                                           border border-zinc-300
                                           bg-white px-4 py-2.5
                                           dark:border-zinc-700
                                           dark:bg-zinc-800"
                                >

                                    <option value="">
                                        -- Pilih Kategori --
                                    </option>

                                    @foreach ($this->daftarKategori as $kategori)

                                        <option value="{{ $kategori->id }}">
                                            {{ $kategori->nama_kategori }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('kategori_id')

                                    <div
                                        class="mt-1 text-sm
                                               text-red-500"
                                    >
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- JUMLAH --}}
                            <div>

                                <label
                                    class="mb-2 block
                                           text-sm font-medium
                                           dark:text-white"
                                >
                                    Jumlah
                                </label>

                                <input
                                    wire:model="jumlah"
                                    type="number"
                                    min="1"
                                    step="1"
                                    placeholder="Contoh: 50000"
                                    class="w-full rounded-lg
                                           border border-zinc-300
                                           bg-white px-4 py-2.5
                                           dark:border-zinc-700
                                           dark:bg-zinc-800"
                                >

                                @error('jumlah')

                                    <div
                                        class="mt-1 text-sm
                                               text-red-500"
                                    >
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- KETERANGAN --}}
                            <div>

                                <label
                                    class="mb-2 block
                                           text-sm font-medium
                                           dark:text-white"
                                >
                                    Keterangan
                                </label>

                                <textarea
                                    wire:model="keterangan"
                                    rows="3"
                                    placeholder="Opsional"
                                    class="w-full rounded-lg
                                           border border-zinc-300
                                           bg-white px-4 py-2.5
                                           dark:border-zinc-700
                                           dark:bg-zinc-800"
                                ></textarea>

                            </div>

                        </div>


                        <div
                            class="shrink-0 flex justify-end gap-3
                            border-t border-zinc-200 bg-white
                            px-5 py-4
                            dark:border-zinc-700 dark:bg-zinc-900
                            sm:px-6"
                        >

                            <button
                                type="button"
                                wire:click="tutupModal"
                                class="rounded-lg
                                       border border-zinc-300
                                       px-4 py-2
                                       dark:border-zinc-700"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="rounded-lg bg-blue-600
                                       px-5 py-2
                                       font-medium text-white
                                       hover:bg-blue-700"
                            >
                                {{ $editingId
                                    ? 'Simpan Perubahan'
                                    : 'Simpan Transaksi' }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        @endif

    </div>

</div>