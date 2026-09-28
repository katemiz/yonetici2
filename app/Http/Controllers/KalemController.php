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
        $bina = Bina::find($request->id);
        $kalem = false;

        if ($bina->user_id !== Auth::id()) {
            abort('404');
        }

        if ($request->kalemid) {
            $kalem = Kalem::find($request->kalemid);
        }

        return view('bina.kalem-form', [
            'bina' => $bina,
            'kalem' => $kalem,
        ]);
    }

    public function add(Request $req)
    {
        $props['user_id'] = Auth::id();
        $props['bina_id'] = $req->id;
        $props['title'] = $req->input('title');

        Kalem::create($props);

        return redirect()->route('kalemler', [
            'id' => $req->id,
        ]);
    }

    public function update(Request $req)
    {
        $props['user_id'] = Auth::id();
        $props['bina_id'] = $req->id;
        $props['title'] = $req->input('title');

        Kalem::find($req->kalemid)->update($props);

        return redirect()->route('kalemler', [
            'id' => $req->id,
        ]);
    }

    public function destroy(int $id, int $kalemid)
    {
        $bina = Bina::query()
            ->where('user_id', Auth::id())
            ->findOrFail($id);
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
