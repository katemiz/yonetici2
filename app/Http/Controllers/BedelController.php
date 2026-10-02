<?php

namespace App\Http\Controllers;

use App\Models\Bedel;
use App\Models\Bina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BedelController extends Controller
{
    public $notification = false;
    public $bina = false;
    public $bedel = false;

    public $tur_secenek = [
        'SABIT' => 'Sabit Bedelli Ödeme',
        'SAYAC' => 'Okumaya Dayalı Ödeme (Sayaçlı)',
    ];

    public $units = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->bina = Bina::query()
                ->accessibleTo(Auth::user())
                ->findOrFail($request->id);

            $this->units = [
                $this->bina->pbirimi => $this->bina->pbirimi,
                'kalori' => $this->bina->pbirimi . '/Kalori',
                'kWh' => 'Kilowatt-saat',
                'm3' => $this->bina->pbirimi . '/m<sup>3</sup>',
            ];
            return $next($request);
        });
    }

    public function form(Request $request)
    {
        $bedelId = $request->route('bedelid');
        if ($bedelId) {
            $this->bedel = $this->bina->bedeller()->findOrFail($bedelId);
        }

        return Inertia::render('BedelForm', [
            'bina' => [
                'id' => $this->bina->id,
                'name' => $this->bina->name,
            ],
            'bedel' => $this->bedel ? [
                'id' => $this->bedel->id,
                'title' => $this->bedel->title,
                'tur' => $this->bedel->tur,
                'unit' => $this->bedel->unit,
                'bedel' => $this->bedel->bedel,
            ] : null,
            'tur_secenek' => $this->tur_secenek,
            'units' => $this->units,
            'oldInput' => [
                'title' => old('title'),
                'tur' => old('tur'),
                'bedel' => old('bedel'),
                'birim' => old('birim'),
            ],
        ]);
    }

    public function add(Request $req)
    {
        $props = $this->readFormValues($req);
        Bedel::create($props);

        return redirect()->route('bedeller', [
            'id' => $req->id,
        ]);
    }

    public function upd(Request $req)
    {
        $props = $this->readFormValues($req);
        $this->bina->bedeller()->findOrFail($req->bedelid)->update($props);

        return redirect()->route('bedeller', [
            'id' => $req->id,
        ]);
    }

    public function readFormValues($req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $props['user_id'] = $bina->user_id;
        $props['bina_id'] = $bina->id;
        $props['title'] = $req->input('title');
        $props['tur'] = $req->input('tur');
        $props['unit'] = $req->input('birim');
        $props['bedel'] = $req->input('bedel');

        return $props;
    }
}
