<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard.index')->name('dashboard');
    Route::livewire('/akun', 'pages::akun.index')->name('akun');
    Route::livewire('/kategori', 'pages::kategori.index')->name('kategori');
    Route::livewire('/transaksi', 'pages::transaksi.index')->name('transaksi');
    Route::livewire('/laporan', 'pages::laporan.index')->name('laporan');
    });

require __DIR__.'/settings.php';
