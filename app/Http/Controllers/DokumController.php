<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\Kayit;
use App\Models\Sakin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DokumController extends Controller
{
    public $bina;
    public $sakinler;

    public $sayilar = [
        'birler' => [
            0 => '',
            1 => 'bir',
            2 => 'iki',
            3 => 'üç',
            4 => 'dört',
            5 => 'beş',
            6 => 'altı',
            7 => 'yedi',
            8 => 'sekiz',
            9 => 'dokuz',
        ],

        'onlar' => [
            0 => '',
            1 => 'on',
            2 => 'yirmi',
            3 => 'otuz',
            4 => 'kırk',
            5 => 'elli',
            6 => 'altmış',
            7 => 'yetmiş',
            8 => 'seksen',
            9 => 'doksan',
        ],

        'yuzler' => [
            0 => '',
            1 => 'yüz',
            2 => 'iki yüz',
            3 => 'üç yüz',
            4 => 'dört yüz',
            5 => 'beş yüz',
            6 => 'altı yüz',
            7 => 'yedi yüz',
            8 => 'sekiz yüz',
            9 => 'dokuz yüz',
        ],
    ];

    public function __construct()
    {
        $this->middleware('auth')->except('makbuz');
        $this->middleware(function ($request, $next) {
            if (!session('bina_id')) {
                return redirect()->route('binalar');
            }

            $this->bina = Bina::find(session('bina_id'));

            if ($this->bina->user_id !== Auth::id()) {
                abort('403');
            }

            foreach ($this->bina->sakinler as $sakin) {
                $this->sakinler[$sakin->id] =
                    $sakin->name . ' ' . $sakin->lastname;
            }

            return $next($request);
        })->except('makbuz');
    }

    public function initialize()
    {
        if (!session('bina_id')) {
            return redirect()->route('binalar');
        }

        $this->bina = Bina::find(session('bina_id'));

        if ($this->bina->user_id !== Auth::id()) {
            abort('403');
        }

        foreach ($this->bina->sakinler as $sakin) {
            $this->sakinler[$sakin->id] = $sakin->name . ' ' . $sakin->lastname;
        }
    }

    public function dokum(Request $request)
    {
        return view('dokum', [
            'notification' => false,
            'bina' => $this->bina,
            'sakinler' => $this->sakinler,
            'ozet' => $this->ozet(),
            'gelirler' => $this->gelirler(),
            'giderler' => $this->giderler(),
        ]);
    }

    public function sayac(Request $request)
    {
        return view('kayit.okumalar-gor', [
            'notification' => false,
            'bina' => $this->bina,
            'sakinler' => $this->sakinler,
            'ozet' => $this->ozet(),
            'gelirler' => $this->gelirler(),
            'giderler' => $this->giderler(),
        ]);
    }

    public function makbuz(Request $request)
    {
        $recordId = $request->route('record');

        if ($request->session()->has('resident_id')) {
            $resident = Sakin::query()
                ->whereKey($request->session()->get('resident_id'))
                ->where('is_active', true)
                ->firstOrFail();
            $building = Bina::findOrFail($request->session()->get('resident_bina_id'));

            abort_unless($resident->bina_id === $building->id, 403);
            abort_unless($recordId, 403);

            $record = Kayit::query()
                ->whereKey($recordId)
                ->where('bina_id', $building->id)
                ->where('sakin_id', $resident->id)
                ->whereIn('tur', ['alacak', 'gelir'])
                ->firstOrFail();

            $this->bina = $building;
            $this->sakinler = [
                $resident->id => $resident->name . ' ' . $resident->lastname,
            ];
        } else {
            abort_unless(Auth::check(), 403);

            $initialization = $this->initialize();
            if ($initialization instanceof \Illuminate\Http\RedirectResponse) {
                return $initialization;
            }

            $record = $recordId
                ? Kayit::query()
                    ->whereKey($recordId)
                    ->where('bina_id', $this->bina->id)
                    ->firstOrFail()
                : false;
        }

        $yazi = $record ? $this->numberToText($record->tutar) : '';

        return view('makbuz', [
            'notification' => false,
            'bina' => $this->bina,
            'sakinler' => $this->sakinler,
            'record' => $record,
            'yazi' => $yazi,
        ]);
    }

    public function numberToText($number)
    {
        if ($number < 10) {
            return $this->sayilar['birler'][$number];
        }

        if ($number < 100 && $number > 9) {
            return $this->sayilar['onlar'][substr($number, 0, 1)] .
                ' ' .
                $this->sayilar['birler'][substr($number, -1)];
        }

        if ($number < 1000 && $number > 99) {
            return $this->sayilar['yuzler'][substr($number, 0, 1)] .
                ' ' .
                $this->sayilar['onlar'][substr($number, 1, 1)] .
                ' ' .
                $this->sayilar['birler'][substr($number, -1)];
        }

        $rakamlar = strlen($number);

        switch (substr($rakamlar, -1)) {
            case '1':
                $bir = 'bir';
                break;

            case '2':
                $bir = 'iki';
                break;

            case '3':
                $bir = 'üç';
                break;

            case '14':
                $bir = 'dört';
                break;
        }

        switch (substr($rakamlar, -2, 1)) {
            case '1':
                $on = 'on';
                break;

            case '2':
                $on = 'yirmi';
                break;

            case '3':
                $on = 'otuz';
                break;

            case '4':
                $on = 'kırk';
                break;
        }

        switch (substr($rakamlar, -3, 1)) {
            case '1':
                $yuz = 'yüz';
                break;

            case '2':
                $yuz = 'on';
                break;

            case '3':
                $yuz = 'on';
                break;

            case '4':
                $yuz = 'on';
                break;
        }
    }

    public function ozet()
    {
        // Alacaklar
        $alacak_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'alacak'],
        ])->sum('tutar');

        // Verecekler - Faturalar
        $verecek_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'verecek'],
        ])->sum('tutar');

        // Gelirler
        $gelirler_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'gelir'],
        ])->sum('tutar');

        // Giderler
        $giderler_top_tutar = Kayit::where([
            ['bina_id', '=', $this->bina->id],
            ['tur', '=', 'gider'],
        ])->sum('tutar');

        $nakit = $gelirler_top_tutar - $giderler_top_tutar;

        $ozet['toplam_alacak'] = number_format($alacak_top_tutar, 2, ',', ' ');
        $ozet['toplam_borc'] = number_format($verecek_top_tutar, 2, ',', ' ');
        $ozet['toplam_gelir'] = number_format($gelirler_top_tutar, 2, ',', ' ');
        $ozet['toplam_gider'] = number_format($giderler_top_tutar, 2, ',', ' ');
        $ozet['nakit'] = number_format($nakit, 2, ',', ' ');

        return (object) $ozet;
    }

    public function gelirler()
    {
        return Kayit::where('bina_id', $this->bina->id)
            ->where('tur', '=', 'gelir')
            ->get();
    }

    public function giderler()
    {
        return Kayit::where('bina_id', $this->bina->id)
            ->where('tur', '=', 'gider')
            ->get();
    }
}
