<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama_akun', 100);
            $table->string('jenis_akun', 30); // Bank, Tunai, E-Wallet
            $table->decimal('saldo_awal', 18, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'jenis_akun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};
