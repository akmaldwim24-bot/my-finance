<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Models\Kategori;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
    'name' => $input['name'],
    'email' => $input['email'],
    'password' => $input['password'],
]);

$kategoriDefault = [
    [
        'nama_kategori' => 'Gaji',
        'jenis' => 'Pemasukan',
    ],
    [
        'nama_kategori' => 'Bonus',
        'jenis' => 'Pemasukan',
    ],
    [
        'nama_kategori' => 'Pendapatan Lain',
        'jenis' => 'Pemasukan',
    ],
    [
        'nama_kategori' => 'Makan & Minum',
        'jenis' => 'Pengeluaran',
    ],
    [
        'nama_kategori' => 'Transportasi',
        'jenis' => 'Pengeluaran',
    ],
    [
        'nama_kategori' => 'Belanja',
        'jenis' => 'Pengeluaran',
    ],
    [
        'nama_kategori' => 'Tagihan',
        'jenis' => 'Pengeluaran',
    ],
    [
        'nama_kategori' => 'Lain-lain',
        'jenis' => 'Pengeluaran',
    ],
];

foreach ($kategoriDefault as $kategori) {
    Kategori::create([
        'user_id' => $user->id,
        'nama_kategori' => $kategori['nama_kategori'],
        'jenis' => $kategori['jenis'],
    ]);
}

return $user;
    }
}
