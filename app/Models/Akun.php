<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Akun extends Model
{
    protected $table = 'akun';

    protected $fillable = [
        'user_id',
        'nama_akun',
        'jenis_akun',
        'saldo_awal',
    ];

    protected function casts(): array
    {
        return [
            'saldo_awal' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'akun_id');
    }

    public function getSaldoAttribute(): float
    {
        $mutasi = $this->transaksi()
            ->join('kategori', 'transaksi.kategori_id', '=', 'kategori.id')
            ->selectRaw("COALESCE(SUM(CASE WHEN kategori.jenis = 'Pemasukan' THEN transaksi.jumlah WHEN kategori.jenis = 'Pengeluaran' THEN -transaksi.jumlah ELSE 0 END), 0) AS total")
            ->value('total');

        return (float) $this->saldo_awal + (float) $mutasi;
    }
}
