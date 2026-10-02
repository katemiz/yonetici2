<?php

namespace App\Http\Controllers;

use App\Models\Bedel;
use App\Models\Bina;
use App\Models\Okuma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OkumaController extends Controller
{
    public $bina;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->bina = Bina::query()
                ->accessibleTo(Auth::user())
                ->findOrFail($request->id);
            return $next($request);
        });
    }

    public function durum(Request $request)
    {
        $okumalar = [];

        $okumali_bedeller = Bedel::query()
            ->where('bina_id', '=', $request->id)
            ->where('tur', '=', 'SAYAC')
            ->get();

        foreach ($this->bina->sakinler as $sakin) {
            foreach ($okumali_bedeller as $sayac) {
                $okumalar[$sakin->id] = Okuma::query()
                    ->where('bina_id', '=', $request->id)
                    ->where('sakin_id', '=', $sakin->id)
                    ->where('bedel_id', '=', $sayac->id)
                    ->get();
            }
        }

        if ($request->bedelid) {
            $this->bedel = Okuma::find($request->bedelid);
        }

        // dd($okumalar);

        return view('bina.okuma-liste', [
            'bina' => $this->bina,
            'okumalar' => $okumalar,
            'okumali_bedeller' => $okumali_bedeller,
        ]);
    }

    public function form(Request $request)
    {
        return view('bina.okuma-form', [
            'bina' => $this->bina,
        ]);
    }
}
