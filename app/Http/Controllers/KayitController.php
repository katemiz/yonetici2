<?php

namespace App\Http\Controllers;

use App\Models\Kayit;
use App\Models\Bedel;
use App\Models\Bina;
use App\Models\Dosya;
use App\Models\Okuma;
use App\Models\Sakin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class KayitController extends Controller
{
    public $bina;
    public $kayit = false;
    public $okuma = false;
    public $tutarlar = false;
    public $okumali_bedeller;
    public $sabit_bedeller;
    public $toplam_sabit_aidat;
    public $sabit_dokumu;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            // if (!session('bina_id')) {
            //     return redirect()->route('binalar');
            // }

            $this->bina = Bina::find(session('bina_id'));

            $this->sabitBedeller();
            $this->okumali_bedeller = $this->okumaliBedeller();

            if ($this->bina->user_id !== Auth::id()) {
                abort('403');
            }

            if ($request->tur == 'aidat') {
                $this->calculateAidatlar();
            }

            return $next($request);
        });
    }

    public function kayitForm(Request $request)
    {
        if ($request->tur === 'gelir') {
            return Inertia::render('GelirForm', [
                'bina' => [
                    'name' => $this->bina->name,
                    'pbirimi' => $this->bina->pbirimi,
                ],
            ]);
        }

        return view('kayit.kayit-form', [
            'bina' => $this->bina,
            'kayit' => $this->kayit,
            'tur' => $request->tur,
            'tutarlar' => $this->tutarlar,
            'okumali_bedeller' => $this->okumali_bedeller,
        ]);
    }

    public function okuma(Request $request)
    {
        return view('kayit.okuma-form', [
            'bina' => $this->bina,
            'okuma' => $this->okuma,
            'tur' => $request->tur,
            'tutarlar' => $this->tutarlar,
            'okumali_bedeller' => $this->okumali_bedeller,
        ]);
    }

    public function kayitAdd(Request $req)
    {
        $req->validate([
            'spending_category' => [
                Rule::requiredIf(in_array($req->tur, ['gider', 'fatura'], true)),
                Rule::in(Kayit::SPENDING_CATEGORIES),
            ],
        ]);

        $props['user_id'] = Auth::id();
        $props['bina_id'] = session('bina_id');
        $props['sakin_id'] = 0;
        $props['remarks'] = $req->input('editor_data');

        if ($req->tur == 'aidat') {
            $bina = Bina::find(session('bina_id'));

            $donem_exp = explode('-', $req->input('donem'));

            $aylar['01'] = 'Ocak';
            $aylar['02'] = 'Şubat';
            $aylar['03'] = 'Mart';
            $aylar['04'] = 'Nisan';
            $aylar['05'] = 'Mayıs';
            $aylar['06'] = 'Haziran';
            $aylar['07'] = 'Temmuz';
            $aylar['08'] = 'Ağustos';
            $aylar['09'] = 'Eylül';
            $aylar['10'] = 'Ekim';
            $aylar['11'] = 'Kasım';
            $aylar['12'] = 'Aralık';

            $aciklama =
                $aylar[$donem_exp['1']] .
                ' ' .
                $donem_exp['0'] .
                ' dönemi aidatı';

            $props['tur'] = 'alacak';
            $props['aciklama'] = $aciklama;
            $props['donem'] = $req->input('donem');
            $props['son_odeme'] = '';

            foreach ($bina->active_sakinler as $sakin) {
                $props['sakin_id'] = $sakin->id;
                $props['tutar'] = $this->tutarlar[$sakin->id];

                $props['dokum'] = json_encode($this->sabit_dokumu[$sakin->id]);

                $kayit = Kayit::create($props);
                $this->addFiles($req, $kayit->id);
            }

            return redirect()->route('durum', ['tur' => 'alacaklar']);
        }

        $tutar = str_replace(',','.',$req->input('tutar'));

        if ($req->tur == 'alacak') {
            $props['sakin_id'] = $req->input('borclu');
            $props['tur'] = 'alacak';
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = date('Y-m-d', time());
            $props['tutar'] = $tutar;
            $props['son_odeme'] = $req->input('sonodeme');

            $kayit = Kayit::create($props);
            $this->addFiles($req, $kayit->id);

            return redirect()->route('durum', ['tur' => 'alacaklar']);
        }

        if ($req->tur == 'fatura') {
            $props['tur'] = 'verecek';
            $props['spending_category'] = $req->input('spending_category');
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = '';
            $props['tutar'] = $tutar;
            $props['son_odeme'] = $req->input('sonodeme');

            $kayit = Kayit::create($props);
            $this->addFiles($req, $kayit->id);

            return redirect()->route('durum', ['tur' => 'verecekler']);
        }

        if ($req->tur == 'gider') {
            $props['tur'] = 'gider';
            $props['spending_category'] = $req->input('spending_category');
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = '';
            $props['tutar'] = $tutar;
            $props['son_odeme'] = date('Y-m-d', time());

            $kayit = Kayit::create($props);
            $this->addFiles($req, $kayit->id);

            return redirect()->route('durum', ['tur' => 'giderler']);
        }

        if ($req->tur == 'gelir') {
            $props['tur'] = 'gelir';
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = '';
            $props['tutar'] = $tutar;
            $props['son_odeme'] = date('Y-m-d', time());

            $kayit = Kayit::create($props);
            $this->addFiles($req, $kayit->id);

            return redirect()->route('durum', ['tur' => 'gelirler']);
        }
    }

    public function okumaAdd(Request $req)
    {
        $req->validate([
            'reading_type' => 'required',
        ]);

        $props['user_id'] = Auth::id();
        $props['bina_id'] = session('bina_id');
        $props['bedel_id'] = $req->input('reading_type');

        $bina = Bina::find(session('bina_id'));

        $donem_exp = explode('-', $req->input('donem'));

        foreach ($bina->sakinler as $sakin) {
            $degisken_okuma = 'okuma_' . $sakin->id;
            $degisken_tarih = 'donem_' . $sakin->id;

            if (
                $req->input($degisken_okuma) > 0 &&
                $req->input($degisken_tarih)
            ) {
                $props['sakin_id'] = $sakin->id;
                $props['son_okuma'] = $req->input($degisken_okuma);
                $props['note'] = $req->input('note_' . $sakin->id);

                Okuma::create($props);
            }
        }

        return redirect()->route('durum', ['tur' => 'alacaklar']);
    }

    public function sabitBedeller()
    {
        $sabit_bedeller = Bedel::where([
            ['bina_id', '=', session('bina_id')],
            ['tur', '=', 'SABIT'],
        ]);

        $this->toplam_sabit_aidat = $sabit_bedeller->sum('bedel');

        foreach ($sabit_bedeller->get() as $v) {
            $this->sabit_bedeller[$v->title] = $v->bedel;
        }

        return true;
    }

    public function okumaliBedeller()
    {
        $okumali_bedeller = Bedel::where([
            ['bina_id', '=', session('bina_id')],
            ['tur', '=', 'SAYAC'],
        ])->get();

        //dd($okumali_bedeller);

        return $okumali_bedeller;
    }

    public function calculateAidatlar()
    {
        foreach ($this->bina->active_sakinler as $sakin) {

            $this->tutarlar[$sakin->id] = round(
                ($sakin->payratio * $this->toplam_sabit_aidat) / 100,
                0
            );

            foreach ($this->sabit_bedeller as $kalem_name => $kalem_deger) {
                $this->sabit_dokumu[$sakin->id][$kalem_name] = round(
                    ($sakin->payratio * $kalem_deger) / 100,
                    0
                );
            }
        }

        return true;
    }

    public function addFiles($req, $id)
    {

        if ($req->has('dosyalar')) {
            foreach ($req->file('dosyalar') as $dosya) {
                if (strlen($dosya->getMimeType()) > 32) {
                    $filename = '/usr' . Auth::id() . '/file/other';
                } else {
                    $filename =
                        '/usr' . Auth::id() . '/' . $dosya->getMimeType();
                }

                $saved_dir = Storage::disk('local')->put($filename, $dosya);
                $this->saveRecord($dosya, $id, $saved_dir);
            }
        }
    }

    public function dosyaEkle(Request $request)
    {
        $this->addFiles($request, request('id'));

        return redirect()->route('durum', ['tur' => request('tur')]);
    }

    public function saveRecord($dosya, $kayit_id, $saved_dir)
    {
        $dosya_data = [
            'kayit_id' => $kayit_id,
            'filename' => $dosya->getClientOriginalName(),
            'stored_as' => $saved_dir,
        ];

        Dosya::create($dosya_data);
    }



    public function kayitGor(){


        if ( empty(session()->get(key: 'bina_id'))) {
            redirect('/bina-list');
        }


        $bina = Bina::find(session()->get(key: 'bina_id'));
        $kayit = Kayit::find(request('id'));

        $yerlesenler = Sakin::where('bina_id','=',$bina['id'])->get()->toArray();

        foreach ($yerlesenler as $yerlesen) {
            $sakinler[$yerlesen['id']] = $yerlesen;
        }

        //dd(vars: $sakinler);

        return view('kayit.kayit-gor',[
            'kayit' => $kayit,
            'bina' => $bina,
            'sakinler' => $sakinler
        ]);



    }
}
