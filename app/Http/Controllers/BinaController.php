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
        return Bina::query()->accessibleTo(Auth::user())->get();
    }

    public function index(Request $request)
    {
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        abort_unless(in_array($sort, ['name', 'created_at'], true), 422);
        abort_unless(in_array($direction, ['asc', 'desc'], true), 422);

        $binalar = Bina::query()
            ->accessibleTo(Auth::user())
            ->when($request->filled('query'), fn ($query) => $query->where(
                'name',
                'like',
                '%' . $request->string('query') . '%'
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
            'query' => $request->query('query', ''),
            'sort' => $sort,
            'direction' => $direction,
            'success' => session('success'),

        ]);
    }

    public function view(int $id)
    {
        $bina = Bina::query()
            ->accessibleTo(Auth::user())
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
            ->accessibleTo(Auth::user())
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
            ->accessibleTo(Auth::user())
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
        $bina = $request->route('id')
            ? Bina::query()->accessibleTo(Auth::user())->findOrFail($request->route('id'))
            : null;

        return Inertia::render('BinaForm', [
            'bina' => $bina ? [
                'id' => $bina->id,
                'name' => $bina->name,
                'address' => $bina->address,
                'city' => $bina->city,
                'pbirimi' => $bina->pbirimi,
                'resident_access_configured' => !empty($bina->resident_access_code),
            ] : null,
            'paralar' => $this->paralar,
            'oldInput' => [
                'binaname' => old('binaname'),
                'binaaddress' => old('binaaddress'),
                'binacity' => old('binacity'),
                'parabirimi' => old('parabirimi'),
                'resident_access_code' => old('resident_access_code'),
            ],
        ]);
    }

    public function addBina(Request $req)
    {
        if (!Auth::user()->canCreateBuilding()) {
            return back()->withErrors(['quota' => 'Bina kotanız dolmuştur.']);
        }
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

        $props['name'] = $req->input('binaname');
        $props['pbirimi'] = $req->input('parabirimi');
        $props['address'] = $req->input('binaaddress');
        $props['city'] = $req->input('binacity');
        if ($req->filled('resident_access_code')) {
            $props['resident_access_code'] = Hash::make($req->input('resident_access_code'));
        }

        Bina::query()
            ->accessibleTo(Auth::user())
            ->findOrFail($req->id)
            ->update($props);

        return redirect()->route('binaview', ['id' => $req->id]);
    }

    public function ayarView(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);

        return view('bina.bina-ayar-view', [
            'notification' => false,
            'bina' => $bina,
        ]);
    }

    public function ayarForm(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);

        return view('bina.bina-ayar-form', [
            'notification' => false,
            'bina' => $bina,
        ]);
    }

    public function ayarAdd(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $props['bina_id'] = $bina->id;
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

        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $ayarlar = Ayarlar::query()->where('bina_id', $bina->id)->findOrFail($req->ayarid);
        $ayarlar->update($props);

        return view('bina.bina-ayar-view', [
            'notification' => [
                'type' => 'is-success',
                'message' => 'Ayarlar güncellenmiştir',
            ],
            'bina' => $bina,
        ]);
    }

    public function selectActive($id)
    {
        $bina = Bina::query()
            ->accessibleTo(Auth::user())
            ->findOrFail($id);

        session(['selected_bina' => $bina->name, 'bina_id' => $bina->id]);

        return redirect()->route('durum.summary');
    }
}
