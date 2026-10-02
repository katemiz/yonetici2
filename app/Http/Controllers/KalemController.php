<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\Kalem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KalemController extends Controller
{
    public function form(Request $request)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($request->id);
        $kalem = false;

        if ($request->kalemid) {
            $kalem = $bina->kalemler()->findOrFail($request->kalemid);
        }

        return view('bina.kalem-form', [
            'bina' => $bina,
            'kalem' => $kalem,
        ]);
    }

    public function add(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $props['user_id'] = $bina->user_id;
        $props['bina_id'] = $bina->id;
        $props['title'] = $req->input('title');

        Kalem::create($props);

        return redirect()->route('kalemler', [
            'id' => $req->id,
        ]);
    }

    public function update(Request $req)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($req->id);
        $props['user_id'] = $bina->user_id;
        $props['bina_id'] = $bina->id;
        $props['title'] = $req->input('title');

        $bina->kalemler()->findOrFail($req->kalemid)->update($props);

        return redirect()->route('kalemler', [
            'id' => $req->id,
        ]);
    }

    public function destroy(int $id, int $kalemid)
    {
        $bina = Bina::query()->accessibleTo(Auth::user())->findOrFail($id);
        $bina->kalemler()->findOrFail($kalemid)->delete();

        return redirect()->route('kalemler', ['id' => $bina->id])
            ->with('success', 'Harcama kalem tanımı silinmiştir.');
    }

    // public function getKalemler($id)
    // {
    //     return Kalem::query()
    //         ->where('bina_id', '=', $id)
    //         ->get();
    // }
}
