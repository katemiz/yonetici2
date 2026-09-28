<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\Sakin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;

class SakinController extends Controller
{

    public $durum = [
        "1" => 'Güncel Sakin - Halen Oturuyor',
        "0" => 'Geçmiş Sakin - Ayrldı',
    ];

    public function index(Request $request, int $id)
    {
        $bina = Bina::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        abort_unless(in_array($sort, ['name', 'created_at'], true), 422);
        abort_unless(in_array($direction, ['asc', 'desc'], true), 422);

        $sakinler = $bina->sakinler()
            ->where('is_active', 1)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('lastname', 'like', '%' . $search . '%')
                        ->orWhere('door_no', 'like', '%' . $search . '%');
                });
            })
            ->when($sort === 'name', fn ($query) => $query->orderBy('name', $direction)->orderBy('lastname', $direction))
            ->when($sort === 'created_at', fn ($query) => $query->orderBy('created_at', $direction))
            ->paginate(Config::get('constants.table.no_of_results'))
            ->withQueryString();
        $sakinler->getCollection()->transform(fn (Sakin $sakin) => [
            'id' => $sakin->id,
            'name' => $sakin->name,
            'lastname' => $sakin->lastname,
            'created_at' => $sakin->created_at?->format('d.m.Y H:i'),
        ]);

        return Inertia::render('SakinList', [
            'bina' => ['id' => $bina->id, 'name' => $bina->name],
            'sakinler' => $sakinler,
            'search' => $request->query('search', ''),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function formSakin(Request $request)
    {
        $bina = Bina::find($request->id);
        $sakin = false;

        if ($bina->user_id !== Auth::id()) {
            abort('404');
        }

        if ($request->sakinid) {
            $sakin = Sakin::find($request->sakinid);
        }

        return view('sakin.sakin-form', [
            'bina' => $bina,
            'sakin' => $sakin,
            'durum' => $this->durum
        ]);
    }

    public function addSakin(Request $req)
    {
        $props['user_id'] = Auth::id();
        $props['bina_id'] = $req->id;
        $props['name'] = $req->input('isim');
        $props['lastname'] = $req->input('soyisim');
        $props['door_no'] = $req->input('door_no');
        $props['is_evsahibi'] = $req->input('sahiplik');
        $props['payratio'] = $req->input('payratio');
        $props['phone'] = $req->input('telno');
        $props['giris_tarihi'] = $req->input('giristarihi');
        $props['remarks'] = $req->input('editor_data');

        $props['is_active'] = $req->input('status');


        $sakin = Sakin::create($props);
        $bina = Bina::find($req->id);

        return view('sakin.sakin-view', [
            'notification' => [
                'type' => 'is-success',
                'message' => 'Bina sakini eklenmiştir',
            ],
            'bina' => $bina,
            'sakin' => $sakin,
            'durum' => $this->durum
        ]);
    }

    public function updateSakin(Request $req)
    {
        $props['user_id'] = Auth::id();
        $props['bina_id'] = $req->id;
        $props['name'] = $req->input('isim');
        $props['lastname'] = $req->input('soyisim');
        $props['door_no'] = $req->input('door_no');
        $props['is_evsahibi'] = $req->input('sahiplik');
        $props['payratio'] = $req->input('payratio');
        $props['phone'] = $req->input('telno');
        $props['giris_tarihi'] = $req->input('giristarihi');
        $props['remarks'] = $req->input('editor_data');

        $props['is_active'] = $req->input('status');

        Sakin::find($req->sakinid)->update($props);

        return redirect()->route('sakinview', [
            'notification' => [
                'type' => 'is-success',
                'message' => 'Bilgiler güncellenmiştir',
            ],
            'id' => $req->id,
            'sakinid' => $req->sakinid,
            'durum' => $this->durum
        ]);
    }

    public function viewSakin(Request $req)
    {
        $bina = Bina::find($req->id);

        if ($bina->user_id !== Auth::id()) {
            abort('404');
        }

        $sakin = Sakin::find($req->sakinid);

        return view('sakin.sakin-view', [
            'notification' => false,
            'bina' => $bina,
            'sakin' => $sakin,
            'durum' => $this->durum
        ]);
    }
}
