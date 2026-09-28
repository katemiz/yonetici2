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

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        abort_unless(in_array($sort, ['name', 'created_at'], true), 422);
        abort_unless(in_array($direction, ['asc', 'desc'], true), 422);

        $binalar = Bina::query()
            ->where('user_id', Auth::id())
            ->when($request->filled('search'), fn ($query) => $query->where(
                'name',
                'like',
                '%' . $request->string('search') . '%'
            ))
            ->orderBy($sort, $direction)
            ->paginate(Config::get('constants.table.no_of_results'))
            ->withQueryString();
        $binalar->getCollection()->transform(fn (Bina $bina) => [
            'id' => $bina->id,
            'name' => $bina->name,
            'created_at' => $bina->created_at?->format('d.m.Y H:i'),
        ]);

        return Inertia::render('BinaList', [
            'binalar' => $binalar,
            'selectedBinaId' => session('bina_id'),
            'search' => $request->query('search', ''),
            'sort' => $sort,
            'direction' => $direction,
            'success' => session('success'),
        ]);
    }

    public function view(int $id)
    {
        $bina = Bina::query()
            ->where('user_id', Auth::id())
            ->withCount(['sakinler', 'kalemler', 'bedeller'])
            ->findOrFail($id);

        return Inertia::render('BinaView', [
            'bina' => [
                'id' => $bina->id,
                'name' => $bina->name,
                'address' => $bina->address,
                'city' => $bina->city,
                'pbirimi' => $bina->pbirimi,
                'resident_access_configured' => !empty($bina->resident_access_code),
                'created_at' => $bina->created_at?->format('d.m.Y H:i'),
                'created_human' => $bina->carbon_created_at,
                'sakinler_count' => $bina->sakinler_count,
                'kalemler_count' => $bina->kalemler_count,
                'bedeller_count' => $bina->bedeller_count,
            ],
        ]);
    }

    public function bedelList(Request $request, int $id)
    {
        $bina = Bina::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        $bedeller = $bina->bedeller()
            ->orderBy('created_at', 'desc')
            ->paginate(Config::get('constants.table.no_of_results'))
            ->withQueryString();
        $bedeller->getCollection()->transform(fn ($bedel) => [
            'id' => $bedel->id,
            'title' => $bedel->title,
            'type' => $bedel->tur,
            'unit' => $bedel->unit,
            'amount' => $bedel->bedel,
            'created_at' => $bedel->created_at?->format('d.m.Y H:i'),
        ]);

        return Inertia::render('BedelList', [
            'bina' => ['id' => $bina->id, 'name' => $bina->name],
            'bedeller' => $bedeller,
        ]);
    }

    public function kalemList(Request $request, int $id)
    {
        $bina = Bina::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        abort_unless(in_array($sort, ['title', 'created_at'], true), 422);
        abort_unless(in_array($direction, ['asc', 'desc'], true), 422);

        $kalemler = $bina->kalemler()
            ->when($request->filled('search'), fn ($query) => $query->where(
                'title',
                'like',
                '%' . $request->string('search') . '%'
            ))
            ->orderBy($sort, $direction)
            ->paginate(Config::get('constants.table.no_of_results'))
            ->withQueryString();
        $kalemler->getCollection()->transform(fn ($kalem) => [
            'id' => $kalem->id,
            'title' => $kalem->title,
            'created_at' => $kalem->created_at?->format('d.m.Y H:i'),
        ]);

        return Inertia::render('KalemList', [
            'bina' => ['id' => $bina->id, 'name' => $bina->name],
            'kalemler' => $kalemler,
            'search' => $request->query('search', ''),
            'sort' => $sort,
            'direction' => $direction,
            'success' => session('success'),
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

        return redirect()->route('binalar')->with('success', 'Bina tanımlaması yapılmıştır.');
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

        Bina::query()
            ->where('user_id', Auth::id())
            ->findOrFail($req->id)
            ->update($props);

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
        $bina = Bina::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        session(['selected_bina' => $bina->name, 'bina_id' => $bina->id]);

        return redirect()->route('durum', ['tur' => 'ozet']);
    }
}
