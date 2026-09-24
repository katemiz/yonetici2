<?php

use App\Http\Controllers\BedelController;
use App\Http\Controllers\BinaController;
use App\Http\Controllers\DokumController;
use App\Http\Controllers\DosyaController;
use App\Http\Controllers\KalemController;
use App\Http\Controllers\KayitController;
use App\Http\Controllers\OkumaController;
use App\Http\Controllers\SakinController;
use App\Http\Livewire\BedelList;
use App\Http\Livewire\BinaList;
use App\Http\Livewire\BinaView;
use App\Http\Livewire\DurumList;
use App\Http\Livewire\KalemList;
use App\Http\Livewire\SakinList;
use App\Http\Livewire\OkumaList;
use App\Http\Livewire\KararList;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;


use App\Http\Livewire\SayacOkuma;

use App\Http\Controllers\PDFController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('lang/{lang}', [
    'as' => 'lang.switch',
    'uses' => 'App\Http\Controllers\LanguageController@switchLang',
]);

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('/dashboard', [BinaController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/bina-list', BinaList::class)->name('binalar');
    Route::get('/bina-view/{id}', BinaView::class)->name('binaview');
    Route::get('/bina-form', [BinaController::class, 'formBina']);
    Route::get('/bina-form/{id}', [BinaController::class, 'formBina']);
    Route::post('/bina-add', [BinaController::class, 'addBina']);
    Route::post('/bina-update/{id}', [BinaController::class, 'updateBina']);
    Route::get('/bina-ayar/{id}', [BinaController::class, 'ayarView']);
    Route::get('/bina-ayar-form/{id}', [BinaController::class, 'ayarForm']);
    Route::post('/bina-ayar-add/{id}', [BinaController::class, 'ayarAdd']);
    Route::post('/bina-ayar-update/{id}/{ayarid}', [
        BinaController::class,
        'ayarUpdate',
    ]);

    Route::get('/okuma-durum/{id}', [OkumaController::class, 'durum']);
    Route::get('/okuma-form/{id}', [OkumaController::class, 'form']);

    Route::get('/bedel-form/{id}', [BedelController::class, 'form']);
    Route::get('/bedel-form/{id}/{bedelid}', [BedelController::class, 'form']);
    Route::post('/bedel-upd/{id}/{bedelid}', [BedelController::class, 'upd']);

    Route::get('/bedel-list/{id}', BedelList::class)->name('bedeller');
    Route::post('/bedel-add/{id}', [BedelController::class, 'add']);

    Route::get('/kalem-list/{id}', KalemList::class)->name('kalemler');
    Route::get('/kalem-form/{id}', [KalemController::class, 'form']);
    Route::get('/kalem-form/{id}/{kalemid}', [KalemController::class, 'form']);
    Route::post('/kalem-add/{id}', [KalemController::class, 'add']);
    Route::post('/kalem-update/{id}/{kalemid}', [
        KalemController::class,
        'update',
    ]);

    Route::get('/sakin-list/{id}', SakinList::class);
    Route::get('/sakin-form/{id}', [SakinController::class, 'formSakin']);
    Route::get('/sakin-form/{id}/{sakinid}', [
        SakinController::class,
        'formSakin',
    ]);
    Route::post('/sakin-add/{id}', [SakinController::class, 'addSakin']);
    Route::post('/sakin-update/{id}/{sakinid}', [
        SakinController::class,
        'updateSakin',
    ]);
    Route::get('/sakin-view/{id}/{sakinid}', [
        SakinController::class,
        'viewSakin',
    ])->name('sakinview');

    Route::get('/kayit-form/{tur}', [KayitController::class, 'kayitForm']);
    Route::get('/kayit-form/{tur}/{id}', [KayitController::class, 'kayitForm']);

    Route::get('/okuma-form', [KayitController::class, 'okuma']);
    Route::get('/okuma-form/{id}', [KayitController::class, 'okuma']);
    Route::post('/okuma-add', [KayitController::class, 'okumaAdd']);

    Route::post('/kayit-add/{tur}', [KayitController::class, 'kayitAdd']);
    Route::get('/kayit-gor/{id}', [KayitController::class, 'kayitGor']);

    Route::post('/kayit-dosya-add/{id}/{tur}', [
        KayitController::class,
        'dosyaEkle',
    ]);

    Route::get('/kayit-dosya-gor/{id}', [DosyaController::class, 'dosya']);
    Route::get('/select-active/{id}', [BinaController::class, 'selectActive']);
    Route::get('/durum/{tur}', DurumList::class)->name('durum');
    Route::get('/dokum', [DokumController::class, 'dokum'])->name('dokum');
    //Route::get('/dokum', [PDFController::class, 'dokum']);
    Route::get('/sayaclar', OkumaList::class);
    Route::get('/makbuz/{record?}', [DokumController::class, 'makbuz'])->name(
        'makbuz'
    );

    Route::get('/karar/{id?}', KararList::class);

    Route::get('/help', function () {
        return View::make('help');
    });

    Route::get('/makbuzpdf/{record}', [PDFController::class, 'dolumakbuz']);
    Route::get('/aylik-aidatlar', [PDFController::class, 'aylikaidatlar']);


    Route::get('/bosmakbuz', [PDFController::class, 'bosmakbuz']);

    Route::get('/sayac-okuma/{idBedel?}/{idBina?}', SayacOkuma::class);



});
