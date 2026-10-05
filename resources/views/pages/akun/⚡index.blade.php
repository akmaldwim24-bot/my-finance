<?php

use App\Models\Akun;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $nama_akun = '';
    public string $jenis_akun = 'Bank';
    public string $saldo_awal = '0';

    public ?int $editingId = null;

    public bool $showModal = false;

    #[Computed]
    public function daftarAkun()
    {
        return Akun::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function bukaTambah(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $akun = Akun::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->editingId = $akun->id;
        $this->nama_akun = $akun->nama_akun;
        $this->jenis_akun = $akun->jenis_akun;
        $this->saldo_awal = (string) $akun->saldo_awal;

        $this->showModal = true;
    }

    public function simpan(): void
    {
        $data = $this->validate([
            'nama_akun' => ['required', 'string', 'max:100'],
            'jenis_akun' => ['required', 'in:Bank,Tunai,E-Wallet'],
            'saldo_awal' => ['required', 'numeric'],
        ]);

        if ($this->editingId) {

            $akun = Akun::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->editingId);

            $akun->update($data);

            session()->flash('success', 'Akun berhasil diperbarui.');

        } else {

            Akun::create([
                'user_id' => Auth::id(),
                'nama_akun' => $data['nama_akun'],
                'jenis_akun' => $data['jenis_akun'],
                'saldo_awal' => $data['saldo_awal'],
            ]);

            session()->flash('success', 'Akun berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function hapus(int $id): void
    {
        $akun = Akun::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($akun->transaksi()->exists()) {
            session()->flash(
                'error',
                'Akun tidak dapat dihapus karena sudah digunakan pada transaksi.'
            );

            return;
        }

        $akun->delete();

        session()->flash('success', 'Akun berhasil dihapus.');
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
        $this->nama_akun = '';
        $this->jenis_akun = 'Bank';
        $this->saldo_awal = '0';
    }
};
?>

<div class="p-6">

    <div class="mx-auto max-w-7xl">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    Akun / Dompet
                </h1>

                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Kelola rekening bank, uang tunai, dan e-wallet.
                </p>
            </div>

            <button
                wire:click="bukaTambah"
                class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white
                       hover:bg-blue-700"
            >
                + Tambah Akun
            </button>

        </div>


        {{-- NOTIFIKASI --}}
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


        {{-- DAFTAR AKUN --}}
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

            @forelse ($this->daftarAkun as $akun)

                @php
                    $icon = match ($akun->jenis_akun) {
                        'Bank' => '🏦',
                        'Tunai' => '💵',
                        default => '💳',
                    };

                    $border = match ($akun->jenis_akun) {
                        'Bank' => 'border-blue-500',
                        'Tunai' => 'border-green-500',
                        default => 'border-purple-500',
                    };
                @endphp

                <div
                    wire:key="akun-{{ $akun->id }}"
                    class="rounded-2xl border border-zinc-200 border-l-8
                           {{ $border }}
                           bg-white p-6 shadow-sm
                           dark:border-zinc-700 dark:bg-zinc-900"
                >

                    <div class="text-4xl">
                        {{ $icon }}
                    </div>

                    <div class="mt-4 text-xl font-bold text-zinc-900 dark:text-white">
                        {{ $akun->nama_akun }}
                    </div>

                    <div class="mt-2">
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs
                                     text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ $akun->jenis_akun }}
                        </span>
                    </div>

                    <div class="mt-5 text-sm text-zinc-500">
                        Saldo
                    </div>

                    <div class="mt-1 text-2xl font-bold text-green-600">
                        Rp {{ number_format($akun->saldo, 0, ',', '.') }}
                    </div>

                    <div class="mt-1 text-xs text-zinc-400">
                        Saldo awal:
                        Rp {{ number_format((float) $akun->saldo_awal, 0, ',', '.') }}
                    </div>

                    <div class="mt-6 flex gap-2">

                        <button
                            wire:click="edit({{ $akun->id }})"
                            class="flex-1 rounded-lg bg-amber-500 px-3 py-2
                                   text-sm font-medium text-white hover:bg-amber-600"
                        >
                            ✏ Edit
                        </button>

                        <button
                            wire:click="hapus({{ $akun->id }})"
                            wire:confirm="Yakin ingin menghapus akun ini?"
                            class="flex-1 rounded-lg bg-red-600 px-3 py-2
                                   text-sm font-medium text-white hover:bg-red-700"
                        >
                            🗑 Hapus
                        </button>

                    </div>

                </div>

            @empty

                <div class="col-span-full rounded-2xl border border-dashed
                            border-zinc-300 p-12 text-center
                            dark:border-zinc-700">

                    <div class="text-5xl">
                        💰
                    </div>

                    <h2 class="mt-4 text-lg font-semibold dark:text-white">
                        Belum ada akun
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        Tambahkan rekening, uang tunai, atau e-wallet pertama kamu.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- MODAL --}}
        @if ($showModal)

            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">

                <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl
                            dark:bg-zinc-900">

                    <div class="flex items-center justify-between border-b
                                border-zinc-200 px-6 py-4 dark:border-zinc-700">

                        <h2 class="text-lg font-bold dark:text-white">
                            {{ $editingId ? 'Edit Akun' : 'Tambah Akun' }}
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

                            {{-- NAMA --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium dark:text-white">
                                    Nama Akun
                                </label>

                                <input
                                    wire:model="nama_akun"
                                    type="text"
                                    placeholder="Contoh: BCA, Cash, GoPay"
                                    class="w-full rounded-lg border border-zinc-300
                                           bg-white px-4 py-2.5 text-zinc-900
                                           dark:border-zinc-700 dark:bg-zinc-800
                                           dark:text-white"
                                >

                                @error('nama_akun')
                                    <div class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- JENIS --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium dark:text-white">
                                    Jenis Akun
                                </label>

                                <select
                                    wire:model="jenis_akun"
                                    class="w-full rounded-lg border border-zinc-300
                                           bg-white px-4 py-2.5 text-zinc-900
                                           dark:border-zinc-700 dark:bg-zinc-800
                                           dark:text-white"
                                >
                                    <option value="Bank">Bank</option>
                                    <option value="Tunai">Tunai</option>
                                    <option value="E-Wallet">E-Wallet</option>
                                </select>
                            </div>


                            {{-- SALDO AWAL --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium dark:text-white">
                                    Saldo Awal
                                </label>

                                <input
                                    wire:model="saldo_awal"
                                    type="number"
                                    min="0"
                                    step="1"
                                    class="w-full rounded-lg border border-zinc-300
                                           bg-white px-4 py-2.5 text-zinc-900
                                           dark:border-zinc-700 dark:bg-zinc-800
                                           dark:text-white"
                                >

                                @error('saldo_awal')
                                    <div class="mt-1 text-sm text-red-500">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                        </div>


                        <div class="flex justify-end gap-3 border-t
                                    border-zinc-200 px-6 py-4 dark:border-zinc-700">

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