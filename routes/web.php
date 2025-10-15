<?php

use App\Http\Controllers\AntrianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(AntrianController::class)->group(function () {
    Route::get('/antrian', 'index')->name('antrian.index');
    Route::get('/ambil/{layanan}', 'ambilNomor')->name('antrian.ambilNomor');
    Route::get('/loket', 'loket')->name('antrian.loket');
    Route::get('/display', 'viewAntrian')->name('antrian.display');
    Route::get('/antrian/data', 'getData')->name('antrian.data');
    Route::get('/antrian/data_loket', 'getdata_loket')->name('antrian.data_loket');
    Route::get('/antrian/video', 'getVideo')->name('antrian.video');
    Route::post('/antrian/upload-video', 'uploadVideo')->name('antrian.upload_video');
    Route::get('/antrian/refresh', 'refresh')->name('antrian.refresh');
    Route::post('/panggil', 'panggil')->name('antrian.panggil');
    Route::post('/lewati', 'lewati')->name('antrian.lewati');
    Route::post('/reset', 'reset')->name('antrian.reset');
});
