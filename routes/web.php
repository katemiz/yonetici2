<?php

use App\Http\Controllers\BedelController;
use App\Http\Controllers\BinaController;
use App\Http\Controllers\DokumController;
use App\Http\Controllers\DosyaController;
use App\Http\Controllers\DurumController;
use App\Http\Controllers\KalemController;
use App\Http\Controllers\KayitController;
use App\Http\Controllers\OkumaController;
use App\Http\Controllers\SayacOkumaController;
use App\Http\Controllers\SakinController;
use App\Http\Livewire\DurumList;
use App\Http\Livewire\OkumaList;
use App\Http\Livewire\KararList;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


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

Route::get('/', [BinaController::class, 'welcome']);

Route::get('lang/{lang}', [
    'as' => 'lang.switch',
    'uses' => 'App\Http\Controllers\LanguageController@switchLang',
]);

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/bina-list', [BinaController::class, 'index'])->name('binalar');
    Route::get('/bina-view/{id}', [BinaController::class, 'view'])->name('binaview');
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

    Route::get('/bedel-list/{id}', [BinaController::class, 'bedelList'])->name('bedeller');
    Route::post('/bedel-add/{id}', [BedelController::class, 'add']);

    Route::get('/kalem-list/{id}', [BinaController::class, 'kalemList'])->name('kalemler');
    Route::get('/kalem-form/{id}', [KalemController::class, 'form']);
    Route::get('/kalem-form/{id}/{kalemid}', [KalemController::class, 'form']);
    Route::post('/kalem-add/{id}', [KalemController::class, 'add']);
    Route::post('/kalem-update/{id}/{kalemid}', [
        KalemController::class,
        'update',
    ]);
    Route::post('/kalem-delete/{id}/{kalemid}', [
        KalemController::class,
        'destroy',
    ]);

    Route::get('/sakin-list/{id}', [SakinController::class, 'index'])->name('sakinler');
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
    Route::post('/kayit-update/{tur}/{id}', [KayitController::class, 'kayitUpdate']);
    Route::get('/kayit-gor/{id}', [KayitController::class, 'kayitGor']);

    Route::post('/kayit-dosya-add/{id}/{tur}', [
        KayitController::class,
        'dosyaEkle',
    ]);

    Route::get('/kayit-dosya-gor/{id}', [DosyaController::class, 'dosya']);
    Route::get('/select-active/{id}', [BinaController::class, 'selectActive']);
    Route::get('/durum/ozet', [DurumController::class, 'summary'])->name('durum.summary');
    Route::get('/durum/alacaklar', [DurumController::class, 'receivables'])->name('durum.receivables');
    Route::post('/durum/alacaklar/{id}/received', [DurumController::class, 'markReceivableReceived']);
    Route::get('/durum/gelirler', [DurumController::class, 'incomes'])->name('durum.incomes');
    Route::get('/durum/giderler', [DurumController::class, 'expenses'])->name('durum.expenses');
    Route::get('/durum/verecekler', [DurumController::class, 'payables'])->name('durum.payables');
    Route::post('/durum/verecekler/{id}/paid', [DurumController::class, 'markPayablePaid']);
    Route::get('/durum/{tur}', DurumList::class)->name('durum');
    Route::get('/dokum', [DokumController::class, 'dokum'])->name('dokum');
    //Route::get('/dokum', [PDFController::class, 'dokum']);
    Route::get('/sayaclar', OkumaList::class);
    Route::get('/karar/{id?}', KararList::class);

    Route::get('/help', fn () => Inertia::render('Help'));

    Route::get('/makbuzpdf/{record}', [PDFController::class, 'dolumakbuz']);
    Route::get('/aylik-aidatlar', [PDFController::class, 'aylikaidatlar']);


    Route::get('/bosmakbuz', [PDFController::class, 'bosmakbuz']);

    Route::get('/sayac-okuma/{idBedel?}/{idBina?}', [SayacOkumaController::class, 'index'])
        ->name('sayac-okuma');
    Route::post('/sayac-okuma', [SayacOkumaController::class, 'store']);
    Route::post('/sayac-okuma/{id}', [SayacOkumaController::class, 'update']);
    Route::post('/sayac-okuma/{id}/bedel', [SayacOkumaController::class, 'charge']);



});

Route::get('/makbuz/{record?}', [DokumController::class, 'makbuz'])->name('makbuz');
