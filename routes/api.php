<?php

use Bale\Frasasti\Http\Controllers\Api\V1\AduanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Frasasti (v1)
|--------------------------------------------------------------------------
|
| Dimuat oleh FrasastiServiceProvider. Middleware group `bale.api` disediakan
| oleh bale/api; scope per endpoint didaftarkan lewat registerApiScopes().
|
| Catatan: Unlike loker & rakaca, tabel `frasasti_aduans` berada pada koneksi
| landlord (default), bukan database tenant.
|
*/

Route::middleware('bale.api')->prefix('api/v1/frasasti')->name('api.frasasti.v1.')->group(function () {
    Route::get('aduans', [AduanController::class, 'index'])
        ->middleware('scope:frasasti.aduan.read')
        ->name('aduans.index');

    Route::get('aduans/{aduan}', [AduanController::class, 'show'])
        ->middleware('scope:frasasti.aduan.read')
        ->name('aduans.show');
});
