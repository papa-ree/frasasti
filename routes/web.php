<?php

use Bale\Frasasti\Livewire\Pages\Guest\Aduan\Index as AduanIndex;
use Bale\Frasasti\Livewire\Pages\Landlord\AduanMasuk\Form as AduanMasukForm;
use Bale\Frasasti\Livewire\Pages\Landlord\AduanMasuk\Index as AduanMasukIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {

    // Public: Aduan / Bantuan (tanpa login)
    Route::get('bantuan', AduanIndex::class)
        ->middleware('throttle:30,1')
        ->name('frasasti.aduan.index');

    // Landlord: pengelolaan aduan masuk
    Route::middleware(['auth'])->prefix('frasasti')->group(function () {

        Route::group(['middleware' => ['permission:aduan.read']], function () {
            Route::get('aduan-masuk', AduanMasukIndex::class)->name('frasasti.aduan-masuk.index');
        });

        Route::group(['middleware' => ['permission:aduan.update']], function () {
            Route::get('aduan-masuk/{aduan}/edit', AduanMasukForm::class)->name('frasasti.aduan-masuk.edit');
        });

    });
});
