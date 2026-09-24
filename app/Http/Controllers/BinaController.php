<?php

namespace App\Http\Controllers;

use App\Models\Ayarlar;
use App\Models\Bina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class BinaController extends Controller
{
    public $paralar = [
        'TL' => 'Türk Lirası',
        'USD' => 'Amerikan Doları',
        'EUR' => 'Avrupa Para Birimi',
    ];

    public function getBinalar()
    {
        return Bina::query()
            ->where('user_id', '=', Auth::id())
            ->get();
    }

    public function binalar()
    {
        return view('bina.bina-list', [
            'notification' => false,
            'binalar' => $this->getBinalar(),
        ]);
    }

    public function dashboard(Request $request)
    {
        $binalarim = $this->getBinalar();

        if (count($binalarim) == 0) {
            return Inertia::render('Dashboard', [
                'bina_sayisi' => 0,
            ]);
        }

        $bina = $this->resolveActiveBina($binalarim);

        if (!$bina) {
            return redirect()->route('binalar');
        }

        return Inertia::render('Dashboard', [
            'bina_sayisi' => $binalarim->count(),
            'bina' => $bina->loadCount(['sakinler', 'kalemler', 'bedeller']),
        ]);
    }

    public function welcome(Request $request)
    {
        if (!Auth::check()) {
            return Inertia::render('Welcome');
        }

        $binalarim = $this->getBinalar();
        $bina = $this->resolveActiveBina($binalarim);

        return Inertia::render('Welcome', [
            'bina_sayisi' => $binalarim->count(),
            'bina' => $bina?->loadCount(['sakinler', 'kalemler', 'bedeller']),
        ]);
    }

    private function resolveActiveBina($binalarim): ?Bina
    {
        if ($binalarim->isEmpty()) {
            return null;
        }

        $selectedId = session('bina_id');
        $bina = $selectedId ? $binalarim->firstWhere('id', (int) $selectedId) : null;

        if (!$bina && $binalarim->count() === 1) {
            $bina = $binalarim->first();
            $this->selectActive($bina->id);
        }

        return $bina;
    }

    public function formBina(Request $request)
    {
        $bina = false;

        if ($request->id) {
            $sonuc = Bina::find($request->id);

            if ($sonuc->user_id === Auth::id()) {
                $bina = $sonuc;
            }
        }

        return view('bina.bina-form', [
            'bina' => $bina,
            'paralar' => $this->paralar,
        ]);
    }

    public function addBina(Request $req)
    {
        $req->validate([
            'resident_access_code' => ['nullable', 'string', 'max:64'],
        ]);

        $props['user_id'] = Auth::id();
        $props['name'] = $req->input('binaname');
        $props['pbirimi'] = $req->input('parabirimi');
        $props['address'] = $req->input('binaaddress');
        $props['city'] = $req->input('binacity');
        $props['resident_access_code'] = $req->filled('resident_access_code')
            ? Hash::make($req->input('resident_access_code'))
            : null;

        Bina::create($props);

        $q = $this->getBinalar();

        return view('bina.bina-list', [
            'notification' => [
                'type' => 'is-success',
                'message' => 'Bina tanımlaması yapılmıştır',
            ],
            'binalar' => $q->paginate(
                Config::get('constants.table.no_of_results')
            ),
        ]);
    }

    public function updateBina(Request $req)
    {
        $req->validate([
            'resident_access_code' => ['nullable', 'string', 'max:64'],
        ]);

        $props['user_id'] = Auth::id();
        $props['name'] = $req->input('binaname');
        $props['pbirimi'] = $req->input('parabirimi');
        $props['address'] = $req->input('binaaddress');
        $props['city'] = $req->input('binacity');
        if ($req->filled('resident_access_code')) {
            $props['resident_access_code'] = Hash::make($req->input('resident_access_code'));
        }

        Bina::find($req->id)->update($props);

        return redirect()->route('binaview', ['id' => $req->id]);
    }

    public function ayarView(Request $req)
    {
        $bina = Bina::find($req->id);

        return view('bina.bina-ayar-view', [
            'notification' => false,
            'bina' => $bina,
        ]);
    }

    public function ayarForm(Request $req)
    {
        $bina = Bina::find($req->id);

        return view('bina.bina-ayar-form', [
            'notification' => false,
            'bina' => $bina,
        ]);
    }

    public function ayarAdd(Request $req)
    {
        $props['bina_id'] = $req->id;
        $props['para_birimi'] = $req->input('parabirimi');
        $props['yakit'] = $req->input('yakit');
        $props['su'] = $req->input('su');
        $props['sicak_su'] = $req->input('sicaksu');
        $props['elektrik'] = $req->input('elektrik');
        $props['asansor'] = $req->input('asansor');
        $props['hizmetli'] = $req->input('hizmetli');
        $props['vergi'] = $req->input('vergi');
        $props['bakim'] = $req->input('bakim');
        $props['onarim'] = $req->input('onarim');
        $props['aidat'] = $req->input('aidat');

        Ayarlar::create($props);

        $bina = Bina::find($req->id);

        return view('bina.bina-ayar-view', [
            'notification' => [
                'type' => 'is-success',
                'message' => 'Ayarlar eklenmiştir',
            ],
            'bina' => $bina,
        ]);
    }

    public function ayarUpdate(Request $req)
    {
        $props['para_birimi'] = $req->input('parabirimi');
        $props['yakit'] = $req->input('yakit');
        $props['su'] = $req->input('su');
        $props['sicak_su'] = $req->input('sicaksu');
        $props['elektrik'] = $req->input('elektrik');
        $props['asansor'] = $req->input('asansor');
        $props['hizmetli'] = $req->input('hizmetli');
        $props['vergi'] = $req->input('vergi');
        $props['bakim'] = $req->input('bakim');
        $props['onarim'] = $req->input('onarim');
        $props['aidat'] = $req->input('aidat');

        Ayarlar::find($req->ayarid)->update($props);

        return view('bina.bina-ayar-view', [
            'notification' => [
                'type' => 'is-success',
                'message' => 'Ayarlar güncellenmiştir',
            ],
            'bina' => Bina::find($req->id),
        ]);
    }

    public function selectActive($id)
    {
        $bina = Bina::find($id);

        session(['selected_bina' => $bina->name, 'bina_id' => $bina->id]);

        return redirect()->route('durum', ['tur' => 'ozet']);
    }
}
