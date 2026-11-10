<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
            ->accessibleTo(Auth::user())
            ->findOrFail($id);
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        $status = $request->query('status', '1');
        abort_unless(in_array($sort, ['name', 'created_at'], true), 422);
        abort_unless(in_array($direction, ['asc', 'desc'], true), 422);
        abort_unless(in_array($status, ['1', '0'], true), 422);

        $sakinler = $bina->sakinler()
            ->where('is_active', $status)
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
        $sakinler->getCollection()->transform(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'lastname' => $user->lastname,
            'created_at' => $user->created_at?->format('d.m.Y H:i'),
        ]);

        return Inertia::render('SakinList', [
            'bina' => ['id' => $bina->id, 'name' => $bina->name],
            'sakinler' => $sakinler,
            'search' => $request->query('search', ''),
            'sort' => $sort,
            'direction' => $direction,
            'status' => $status,
        ]);
    }

    public function formSakin(Request $request)
    {
        $bina = Bina::query()
            ->accessibleTo(Auth::user())
            ->findOrFail($request->route('id'));

        $sakin = null;
        if ($request->route('sakinid')) {
            $sakin = $bina->sakinler()->findOrFail($request->route('sakinid'));
        }

        return Inertia::render('SakinForm', [
            'bina' => [
                'id' => $bina->id,
                'name' => $bina->name,
            ],
            'sakin' => $sakin ? [
                'id' => $sakin->id,
                'name' => $sakin->name,
                'lastname' => $sakin->lastname,
                'door_no' => $sakin->door_no,
                'is_evsahibi' => (bool) $sakin->is_evsahibi,
                'payratio' => $sakin->payratio,
                'phone' => $sakin->phone,
                'email' => $sakin->email,
                'giris_tarihi' => $sakin->giris_tarihi,
                'remarks' => $sakin->remarks,
                'is_active' => (bool) $sakin->is_active,
            ] : null,
            'durum' => $this->durum,
            'oldInput' => [
                'isim' => old('isim'),
                'soyisim' => old('soyisim'),
                'door_no' => old('door_no'),
                'sahiplik' => old('sahiplik'),
                'payratio' => old('payratio'),
                'telno' => old('telno'),
                'email' => old('email'),
                'giristarihi' => old('giristarihi'),
                'editor_data' => old('editor_data'),
                'status' => old('status'),
            ],
        ]);
    }

    public function addSakin(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $this->validateSakin($req);

        $props['user_id'] = $bina->user_id;
        $props['bina_id'] = $bina->id;
        $props['name'] = $req->input('isim');
        $props['lastname'] = $req->input('soyisim');
        $props['door_no'] = $req->input('door_no');
        $props['is_evsahibi'] = $req->input('sahiplik');
        $props['payratio'] = $req->input('payratio');
        $props['phone'] = $req->input('telno');
        $props['email'] = $req->input('email');
        $props['giris_tarihi'] = $req->input('giristarihi');
        $props['remarks'] = $req->input('editor_data');
        $props['is_active'] = $req->input('status');
        $props['is_sakin'] = true;
        $props['password'] = Hash::make(Str::random(24));

        $user = User::create($props);

        return redirect()->route('sakinview', [
            'id' => $bina->id,
            'sakinid' => $user->id,
        ]);
    }

    public function updateSakin(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $this->validateSakin($req);

        $props['user_id'] = $bina->user_id;
        $props['bina_id'] = $bina->id;
        $props['name'] = $req->input('isim');
        $props['lastname'] = $req->input('soyisim');
        $props['door_no'] = $req->input('door_no');
        $props['is_evsahibi'] = $req->input('sahiplik');
        $props['payratio'] = $req->input('payratio');
        $props['phone'] = $req->input('telno');
        $props['email'] = $req->input('email');
        $props['giris_tarihi'] = $req->input('giristarihi');
        $props['remarks'] = $req->input('editor_data');
        $props['is_active'] = $req->input('status');

        $user = $bina->sakinler()->findOrFail($req->sakinid);
        $user->update($props);

        return redirect()->route('sakinview', [
            'id' => $req->id,
            'sakinid' => $req->sakinid,
        ]);
    }

    private function validateSakin(Request $request): void
    {
        $request->validate([
            'isim' => ['required', 'string', 'max:255'],
            'soyisim' => ['required', 'string', 'max:255'],
            'door_no' => ['required', 'string', 'max:255'],
            'sahiplik' => ['required', 'in:0,1'],
            'payratio' => ['required', 'numeric', 'min:50', 'max:100'],
            'telno' => ['required', 'string', 'max:16'],
            'email' => ['nullable', 'email', 'max:255'],
            'giristarihi' => ['required', 'date'],
            'editor_data' => ['nullable', 'string'],
            'status' => ['required', 'in:0,1'],
        ]);
    }

    public function viewSakin(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $user = $bina->sakinler()->findOrFail($req->sakinid);

        return Inertia::render('SakinView', [
            'bina' => [
                'id' => $bina->id,
                'name' => $bina->name,
            ],
            'sakin' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'lastname' => $user->lastname,
                'door_no' => $user->door_no,
                'is_evsahibi' => (bool) $user->is_evsahibi,
                'phone' => $user->phone,
                'email' => $user->email,
                'giris_tarihi' => $user->giris_tarihi,
                'payratio' => $user->payratio,
                'remarks' => $user->remarks,
                'is_active' => (bool) $user->is_active,
                'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
                'created_human' => $user->created_at?->diffForHumans(),
                'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
                'updated_human' => $user->updated_at?->diffForHumans(),
            ] : null,
            'durum' => $this->durum,
        ]);
    }
}
