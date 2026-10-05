<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['nama_kategori' => 'Gaji', 'jenis' => 'Pemasukan'],
            ['nama_kategori' => 'Bonus', 'jenis' => 'Pemasukan'],
            ['nama_kategori' => 'Pendapatan Lain', 'jenis' => 'Pemasukan'],
            ['nama_kategori' => 'Makan & Minum', 'jenis' => 'Pengeluaran'],
            ['nama_kategori' => 'Transportasi', 'jenis' => 'Pengeluaran'],
            ['nama_kategori' => 'Belanja', 'jenis' => 'Pengeluaran'],
            ['nama_kategori' => 'Tagihan', 'jenis' => 'Pengeluaran'],
            ['nama_kategori' => 'Lain-lain', 'jenis' => 'Pengeluaran'],
        ];

        User::query()->each(function (User $user) use ($defaults) {
            foreach ($defaults as $item) {
                Kategori::firstOrCreate([
                    'user_id' => $user->id,
                    'nama_kategori' => $item['nama_kategori'],
                    'jenis' => $item['jenis'],
                ]);
            }
        });
    }
}
