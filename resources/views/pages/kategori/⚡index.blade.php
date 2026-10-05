<?php

use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $nama_kategori = '';
    public string $jenis = 'Pengeluaran';

    public ?int $editingId = null;
    public bool $showModal = false;

    #[Computed]
    public function kategoriPemasukan()
    {
        return Kategori::query()
            ->where('user_id', Auth::id())
            ->where('jenis', 'Pemasukan')
            ->orderBy('nama_kategori')
            ->get();
    }

    #[Computed]
    public function kategoriPengeluaran()
    {
        return Kategori::query()
            ->where('user_id', Auth::id())
            ->where('jenis', 'Pengeluaran')
            ->orderBy('nama_kategori')
            ->get();
    }

    public function bukaTambah(string $jenis = 'Pengeluaran'): void
    {
        $this->resetForm();
        $this->jenis = $jenis;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $kategori = Kategori::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->editingId = $kategori->id;
        $this->nama_kategori = $kategori->nama_kategori;
        $this->jenis = $kategori->jenis;

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori', 'nama_kategori')
                    ->where(fn ($query) =>
                        $query
                            ->where('user_id', Auth::id())
                            ->where('jenis', $this->jenis)
                    )
                    ->ignore($this->editingId),
            ],
            'jenis' => [
                'required',
                Rule::in(['Pemasukan', 'Pengeluaran']),
            ],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Kategori tersebut sudah tersedia.',
            'jenis.required' => 'Jenis kategori wajib dipilih.',
        ]);

        if ($this->editingId) {
            $kategori = Kategori::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingId);

            $kategori->update($data);

            session()->flash('success', 'Kategori berhasil diperbarui.');
        } else {
            Kategori::create([
                'user_id' => Auth::id(),
                'nama_kategori' => $data['nama_kategori'],
                'jenis' => $data['jenis'],
            ]);

            session()->flash('success', 'Kategori berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();

        unset($this->kategoriPemasukan);
        unset($this->kategoriPengeluaran);
    }

    public function hapus(int $id): void
    {
        $kategori = Kategori::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($kategori->transaksi()->exists()) {
            session()->flash(
                'error',
                'Kategori tidak dapat dihapus karena sudah digunakan pada transaksi.'
            );

            return;
        }

        $kategori->delete();

        unset($this->kategoriPemasukan);
        unset($this->kategoriPengeluaran);

        session()->flash('success', 'Kategori berhasil dihapus.');
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
        $this->nama_kategori = '';
        $this->jenis = 'Pengeluaran';
    }
};
?>

<div class="p-6">

    <div class="mx-auto max-w-7xl">

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    Kategori
                </h1>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Kelola kategori pemasukan dan pengeluaran.
                </p>
            </div>

            <button
                wire:click="bukaTambah"
                class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700"
            >
                + Tambah Kategori
            </button>

        </div>


        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-100 px-4 py-3 text-green-800
                        dark:bg-green-900/30 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-lg bg-red-100 px-4 py-3 text-red-800
                        dark:bg-red-900/30 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif


        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- PEMASUKAN --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5
                        dark:border-zinc-700 dark:bg-zinc-900">

                <div class="mb-5 flex items-center justify-between">

                    <div>
                        <h2 class="text-lg font-bold text-green-600">
                            ↑ Pemasukan
                        </h2>

                        <p class="text-sm text-zinc-500">
                            {{ $this->kategoriPemasukan->count() }} kategori
                        </p>
                    </div>

                    <button
                        wire:click="bukaTambah('Pemasukan')"
                        class="rounded-lg bg-green-600 px-3 py-2 text-sm font-medium
                               text-white hover:bg-green-700"
                    >
                        + Tambah
                    </button>

                </div>

                <div class="space-y-3">

                    @forelse ($this->kategoriPemasukan as $kategori)

                        <div
                            wire:key="pemasukan-{{ $kategori->id }}"
                            class="flex flex-col gap-3 rounded-xl
                                    border border-zinc-200 p-4
                                    dark:border-zinc-700
                                    sm:flex-row sm:items-center sm:justify-between"
                                >

                            <div class="flex w-full min-w-0 items-center gap-3 sm:flex-1">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center
                                            rounded-full bg-green-100 text-xl
                                            dark:bg-green-900/30">
                                    💰
                                </div>

                                <div>
                                    <div class="break-words font-semibold leading-tight text-zinc-900 dark:text-white">
                                        {{ $kategori->nama_kategori }}
                                    </div>

                                    <div class="text-xs text-green-600">
                                        Pemasukan
                                    </div>
                                </div>

                            </div>

                            <div class="flex w-full justify-end gap-2 sm:w-auto sm:shrink-0">

                                <button
                                    wire:click="edit({{ $kategori->id }})"
                                    class="rounded-lg bg-amber-500 px-3 py-2
                                           text-sm text-white hover:bg-amber-600"
                                >
                                    ✏
                                </button>

                                <button
                                    wire:click="hapus({{ $kategori->id }})"
                                    wire:confirm="Yakin ingin menghapus kategori ini?"
                                    class="rounded-lg bg-red-600 px-3 py-2
                                           text-sm text-white hover:bg-red-700"
                                >
                                    🗑
                                </button>

                            </div>

                        </div>

                    @empty

                        <div class="rounded-xl border border-dashed
                                    border-zinc-300 p-8 text-center
                                    text-sm text-zinc-500 dark:border-zinc-700">
                            Belum ada kategori pemasukan.
                        </div>

                    @endforelse

                </div>

            </div>


            {{-- PENGELUARAN --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5
                        dark:border-zinc-700 dark:bg-zinc-900">

                <div class="mb-5 flex items-center justify-between">

                    <div>
                        <h2 class="text-lg font-bold text-red-500">
                            ↓ Pengeluaran
                        </h2>

                        <p class="text-sm text-zinc-500">
                            {{ $this->kategoriPengeluaran->count() }} kategori
                        </p>
                    </div>

                    <button
                        wire:click="bukaTambah('Pengeluaran')"
                        class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium
                               text-white hover:bg-red-700"
                    >
                        + Tambah
                    </button>

                </div>

                <div class="space-y-3">

                    @forelse ($this->kategoriPengeluaran as $kategori)

                        <div
                            wire:key="pengeluaran-{{ $kategori->id }}"
                            class="flex flex-col gap-3 rounded-xl
                                    border border-zinc-200 p-4
                                    dark:border-zinc-700
                                    sm:flex-row sm:items-center sm:justify-between"
                                >

                            <div class="flex w-full min-w-0 items-center gap-3 sm:flex-1">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center
                                            rounded-full bg-red-100 text-xl
                                            dark:bg-red-900/30">
                                    💸
                                </div>

                                <div>
                                    <div class="break-words font-semibold leading-tight text-zinc-900 dark:text-white">
                                        {{ $kategori->nama_kategori }}
                                    </div>

                                    <div class="text-xs text-red-500">
                                        Pengeluaran
                                    </div>
                                </div>

                            </div>

                            <div class="flex w-full justify-end gap-2 sm:w-auto sm:shrink-0">

                                <button
                                    wire:click="edit({{ $kategori->id }})"
                                    class="rounded-lg bg-amber-500 px-3 py-2
                                           text-sm text-white hover:bg-amber-600"
                                >
                                    ✏
                                </button>

                                <button
                                    wire:click="hapus({{ $kategori->id }})"
                                    wire:confirm="Yakin ingin menghapus kategori ini?"
                                    class="rounded-lg bg-red-600 px-3 py-2
                                           text-sm text-white hover:bg-red-700"
                                >
                                    🗑
                                </button>

                            </div>

                        </div>

                    @empty

                        <div class="rounded-xl border border-dashed
                                    border-zinc-300 p-8 text-center
                                    text-sm text-zinc-500 dark:border-zinc-700">
                            Belum ada kategori pengeluaran.
                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- MODAL --}}
        @if ($showModal)

            <div class="fixed inset-0 z-50 flex items-center justify-center
                        bg-black/60 p-4">

                <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl
                            dark:bg-zinc-900">

                    <div class="flex items-center justify-between border-b
                                border-zinc-200 px-6 py-4
                                dark:border-zinc-700">

                        <h2 class="text-lg font-bold dark:text-white">
                            {{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}
                        </h2>

                        <button
                            wire:click="tutupModal"
                            class="text-2xl text-zinc-500 hover:text-zinc-900
                                   dark:hover:text-white"
                        >
                            ×
                        </button>

                    </div>

                    <form wire:submit="simpan">

                        <div class="space-y-5 p-6">

                            <div>

                                <label class="mb-2 block text-sm font-medium dark:text-white">
                                    Nama Kategori
                                </label>

                                <input
                                    wire:model="nama_kategori"
                                    type="text"
                                    placeholder="Contoh: Gaji, Makan, Transportasi"
                                    class="w-full rounded-lg border border-zinc-300
                                           bg-white px-4 py-2.5 text-zinc-900
                                           dark:border-zinc-700 dark:bg-zinc-800
                                           dark:text-white"
                                >

                                @error('nama_kategori')
                                    <div class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div>

                                <label class="mb-2 block text-sm font-medium dark:text-white">
                                    Jenis
                                </label>

                                <select
                                    wire:model="jenis"
                                    class="w-full rounded-lg border border-zinc-300
                                           bg-white px-4 py-2.5 text-zinc-900
                                           dark:border-zinc-700 dark:bg-zinc-800
                                           dark:text-white"
                                >
                                    <option value="Pemasukan">
                                        Pemasukan
                                    </option>

                                    <option value="Pengeluaran">
                                        Pengeluaran
                                    </option>
                                </select>

                                @error('jenis')
                                    <div class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>

                        </div>


                        <div class="flex justify-end gap-3 border-t
                                    border-zinc-200 px-6 py-4
                                    dark:border-zinc-700">

                            <button
                                type="button"
                                wire:click="tutupModal"
                                class="rounded-lg border border-zinc-300
                                       px-4 py-2 dark:border-zinc-700 dark:text-white"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="rounded-lg bg-blue-600 px-5 py-2
                                       font-medium text-white hover:bg-blue-700"
                            >
                                {{ $editingId ? 'Simpan Perubahan' : 'Simpan' }}
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        @endif

    </div>

</div>