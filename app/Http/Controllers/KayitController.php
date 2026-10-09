<?php

namespace App\Http\Controllers;

use App\Models\Kayit;
use App\Models\Bedel;
use App\Models\Bina;
use App\Models\Dosya;
use App\Models\Okuma;
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

            $this->bina = Bina::query()
                ->accessibleTo(Auth::user())
                ->findOrFail(session('bina_id'));

            $this->sabitBedeller();
            $this->okumali_bedeller = $this->okumaliBedeller();

            if ($request->tur == 'aidat') {
                $this->calculateAidatlar();
            }

            return $next($request);
        });
    }

    public function kayitForm(Request $request)
    {
        $recordId = $request->route('id');



        if ($recordId) {
            $this->kayit = Kayit::query()
                ->where('bina_id', $this->bina->id)
                ->findOrFail($recordId);

            $expectedType = match ($request->tur) {
                'gelir' => 'gelir',
                'gider' => 'gider',
                'fatura' => 'verecek',
                'alacak' => 'alacak',
                default => null,
            };

            abort_unless($expectedType && $this->kayit->tur === $expectedType, 404);
        }


        if (in_array($request->tur, ['gider', 'fatura'], true)) {
            return Inertia::render('GiderFaturaForm', [
                'bina' => [
                    'name' => $this->bina->name,
                    'pbirimi' => $this->bina->pbirimi,
                    'kalemler' => $this->bina->kalemler()->pluck('title'),
                ],
                'tur' => $request->tur,
                'kayit' => $this->kayit ? [
                    'id' => $this->kayit->id,
                    'aciklama' => $this->kayit->aciklama,
                    'tutar' => $this->kayit->tutar,
                    'son_odeme' => $this->kayit->son_odeme,
                    'spending_category' => $this->kayit->spending_category,
                    'remarks' => $this->kayit->remarks,
                    'files' => in_array($request->tur, ['gider', 'fatura'], true)
                        ? $this->recordFiles($this->kayit)
                        : [],
                ] : null,
            ]);
        }

        if ($request->tur === 'alacak') {
            return Inertia::render('AlacakForm', [
                'bina' => [
                    'name' => $this->bina->name,
                    'pbirimi' => $this->bina->pbirimi,
                ],
                'residents' => $this->bina->active_sakinler->map(fn ($sakin) => [
                    'id' => $sakin->id,
                    'door_no' => $sakin->door_no,
                    'name' => $sakin->name,
                    'lastname' => $sakin->lastname,
                ])->values(),
                'kayit' => $this->kayit ? [
                    'id' => $this->kayit->id,
                    'sakin_id' => $this->kayit->sakin_id,
                    'aciklama' => $this->kayit->aciklama,
                    'tutar' => $this->kayit->tutar,
                    'son_odeme' => $this->kayit->son_odeme,
                    'remarks' => $this->kayit->remarks,
                    'files' => $this->recordFiles($this->kayit),
                ] : null,
                'oldInput' => [
                    'borclu' => old('borclu'),
                    'aciklama' => old('aciklama'),
                    'tutar' => old('tutar'),
                    'sonodeme' => old('sonodeme'),
                    'editor_data' => old('editor_data'),
                ],
            ]);
        }

        if ($request->tur === 'aidat') {
            return Inertia::render('AidatForm', [
                'bina' => [
                    'name' => $this->bina->name,
                ],
                'residents' => $this->bina->active_sakinler->map(fn ($sakin) => [
                    'door_no' => $sakin->door_no,
                    'name' => $sakin->name,
                    'lastname' => $sakin->lastname,
                    'amount' => $this->tutarlar[$sakin->id],
                ])->values(),
                'period' => now()->toDateString(),
            ]);
        }

        if ($request->tur === 'gelir') {

            return Inertia::render('GelirForm', [
                'bina' => [
                    'name' => $this->bina->name,
                    'pbirimi' => $this->bina->pbirimi,
                ],
                'kayit' => $this->kayit ? [
                    'id' => $this->kayit->id,
                    'aciklama' => $this->kayit->aciklama,
                    'tutar' => $this->kayit->tutar,
                    'remarks' => $this->kayit->remarks,
                    'files' => $this->recordFiles($this->kayit),
                ] : null,
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
        $tur = $req->route('tur');

        $req->validate([
            'spending_category' => [
                Rule::requiredIf(in_array($tur, ['gider', 'fatura'], true)),
                Rule::in(Kayit::SPENDING_CATEGORIES),
            ],
        ]);

        $props['user_id'] = $this->bina->user_id;
        $props['bina_id'] = $this->bina->id;
        $props['sakin_id'] = 0;
        $props['remarks'] = $req->input('editor_data');

        if ($tur == 'aidat') {
            $bina = $this->bina;

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

            return redirect()->route('durum.receivables');
        }

        $tutar = str_replace(',','.',$req->input('tutar'));

        if ($tur == 'alacak') {
            $req->merge([
                'tutar' => str_replace(',', '.', (string) $req->input('tutar')),
            ]);
            $req->validate([
                'borclu' => [
                    'required',
                    Rule::exists('sakinler', 'id')->where(fn ($query) => $query
                        ->where('bina_id', $this->bina->id)
                        ->where('is_active', 1)),
                ],
                'aciklama' => ['required', 'string', 'min:10'],
                'tutar' => ['required', 'numeric'],
                'sonodeme' => ['nullable', 'date'],
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);

            $props['sakin_id'] = $req->input('borclu');
            $props['tur'] = 'alacak';
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = date('Y-m-d', time());
            $props['tutar'] = $tutar;
            $props['son_odeme'] = $req->input('sonodeme');

            $kayit = Kayit::create($props);
            $this->storeRecordMedia($req, $kayit);

            return redirect()->route('durum.receivables');
        }

        if ($tur == 'fatura') {
            $req->validate([
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);

            $props['tur'] = 'verecek';
            $props['spending_category'] = $req->input('spending_category');
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = '';
            $props['tutar'] = $tutar;
            $props['son_odeme'] = $req->input('sonodeme');

            $kayit = Kayit::create($props);
            $this->storeRecordMedia($req, $kayit);

            return redirect()->route('durum.payables');
        }

        if ($tur == 'gider') {
            $req->validate([
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);

            $props['tur'] = 'gider';
            $props['spending_category'] = $req->input('spending_category');
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = '';
            $props['tutar'] = $tutar;
            $props['son_odeme'] = date('Y-m-d', time());

            $kayit = Kayit::create($props);
            $this->storeRecordMedia($req, $kayit);

            return redirect()->route('durum.expenses');
        }

        if ($tur == 'gelir') {
            $req->validate([
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);

            $props['tur'] = 'gelir';
            $props['aciklama'] = $req->input('aciklama');
            $props['donem'] = '';
            $props['tutar'] = $tutar;
            $props['son_odeme'] = date('Y-m-d', time());

            $kayit = Kayit::create($props);
            $this->storeRecordMedia($req, $kayit);

            return redirect()->route('durum.incomes');
        }
    }

    public function kayitUpdate(Request $request, string $tur, int $id)
    {
        $kayit = Kayit::query()
            ->where('bina_id', $this->bina->id)
            ->findOrFail($id);

        $expectedType = match ($tur) {
            'gelir' => 'gelir',
            'gider' => 'gider',
            'fatura' => 'verecek',
            'alacak' => 'alacak',
            default => abort(404),
        };

        abort_unless($kayit->tur === $expectedType, 404);

        $request->validate([
            'spending_category' => [
                Rule::requiredIf(in_array($tur, ['gider', 'fatura'], true)),
                Rule::in(Kayit::SPENDING_CATEGORIES),
            ],
        ]);

        $kayit->aciklama = $request->input('aciklama');
        $kayit->tutar = str_replace(',', '.', $request->input('tutar'));
        $kayit->remarks = $request->input('editor_data');

        if ($tur === 'alacak') {
            $request->merge([
                'tutar' => str_replace(',', '.', (string) $request->input('tutar')),
            ]);
            $request->validate([
                'borclu' => [
                    'required',
                    Rule::exists('sakinler', 'id')->where(fn ($query) => $query
                        ->where('bina_id', $this->bina->id)
                        ->where('is_active', 1)),
                ],
                'aciklama' => ['required', 'string', 'min:10'],
                'tutar' => ['required', 'numeric'],
                'sonodeme' => ['nullable', 'date'],
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);

            $kayit->sakin_id = $request->input('borclu');
            $kayit->son_odeme = $request->input('sonodeme');
        } elseif ($tur === 'fatura') {
            $kayit->spending_category = $request->input('spending_category');
            $kayit->son_odeme = $request->input('sonodeme');
            $request->validate([
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);
        } elseif ($tur === 'gider') {
            $kayit->spending_category = $request->input('spending_category');
            $request->validate([
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);
        } elseif ($tur === 'gelir') {
            $request->validate([
                'dosyalar' => ['sometimes', 'array'],
                'dosyalar.*' => ['file', 'max:10240'],
            ]);
        }

        $kayit->save();
        if (in_array($tur, ['alacak', 'gelir', 'gider', 'fatura'], true)) {
            $this->storeRecordMedia($request, $kayit);
        } else {
            $this->addFiles($request, $kayit->id);
        }

        $statusRoute = match ($tur) {
            'gelir' => 'durum.incomes',
            'gider' => 'durum.expenses',
            'fatura' => 'durum.payables',
            'alacak' => 'durum.receivables',
        };

        return redirect()->route($statusRoute);
    }

    public function okumaAdd(Request $req)
    {
        $req->validate([
            'reading_type' => 'required',
        ]);

        $props['user_id'] = $this->bina->user_id;
        $props['bina_id'] = $this->bina->id;
        $props['bedel_id'] = $req->input('reading_type');

        $bina = $this->bina;

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

        return redirect()->route('durum.receivables');
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

    private function storeRecordMedia(Request $request, Kayit $kayit): void
    {
        $collection = match ($kayit->tur) {
            'alacak' => 'alacak-attachments',
            'gelir' => 'income-attachments',
            'gider' => 'expense-attachments',
            'verecek' => 'payable-attachments',
            default => abort(404),
        };

        foreach ($request->file('dosyalar', []) as $file) {
            $kayit->addMedia($file)->toMediaCollection($collection);
        }
    }

    private function recordFiles(Kayit $kayit): array
    {
        $kayit->loadMissing(['dosyalar', 'media']);
        $collection = match ($kayit->tur) {
            'alacak' => 'alacak-attachments',
            'gelir' => 'income-attachments',
            'gider' => 'expense-attachments',
            'verecek' => 'payable-attachments',
            default => abort(404),
        };

        $legacyFiles = $kayit->dosyalar->map(function (Dosya $file) use ($kayit) {
            $path = Storage::disk('local')->path($file->stored_as);
            $exists = is_file($path);

            return [
                'id' => $file->id,
                'name' => $file->filename,
                'url' => "/kayit-dosya-gor/{$file->id}",
                'deleteUrl' => "/kayit-dosya-delete/{$kayit->id}/{$file->id}",
                'mime' => $exists ? mime_content_type($path) : '',
                'size' => $exists ? $this->formatFileSize(filesize($path)) : '',
            ];
        });

        $mediaFiles = $collection ? $kayit->getMedia($collection)->map(fn ($media) => [
            'id' => $media->id,
            'name' => $media->file_name,
            'url' => "/kayit-media-gor/{$kayit->id}/{$media->id}",
            'deleteUrl' => "/media-delete/{$media->id}",
            'mime' => $media->mime_type,
            'size' => $this->formatFileSize($media->size),
        ]) : collect();

        return $legacyFiles->concat($mediaFiles)->values()->all();
    }

    private function formatFileSize(int $bytes): string
    {
        return $bytes < 1024
            ? "{$bytes} B"
            : number_format($bytes / 1024, 2) . ' KB';
    }

    public function dosyaEkle(Request $request)
    {
        $this->addFiles($request, request('id'));

        $statusRoute = match (request('tur')) {
            'gelirler' => 'durum.incomes',
            'giderler' => 'durum.expenses',
            'verecekler' => 'durum.payables',
            'alacaklar' => 'durum.receivables',
            default => null,
        };

        return $statusRoute
            ? redirect()->route($statusRoute)
            : redirect()->route('durum', ['tur' => request('tur')]);
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



    public function kayitGor(int $id)
    {
        $kayit = Kayit::query()
            ->with(['sakin', 'dosyalar', 'media'])
            ->where('bina_id', $this->bina->id)
            ->findOrFail($id);

        $editType = match ($kayit->tur) {
            'gelir' => 'gelir',
            'gider' => 'gider',
            'verecek' => 'fatura',
            'alacak' => 'alacak',
            default => null,
        };

        return Inertia::render('KayitDetail', [
            'bina' => [
                'name' => $this->bina->name,
                'address' => $this->bina->address,
                'pbirimi' => $this->bina->pbirimi,
            ],
            'record' => [
                'id' => $kayit->id,
                'type' => $kayit->tur,
                'description' => $kayit->aciklama,
                'period' => $kayit->donem,
                'amount' => $kayit->tutar,
                'remarks' => $kayit->remarks,
                'breakdown' => $kayit->dokum ? json_decode($kayit->dokum, true) : null,
                'resident' => $kayit->sakin ? [
                    'name' => trim($kayit->sakin->name . ' ' . $kayit->sakin->lastname),
                    'door_no' => $kayit->sakin->door_no,
                ] : null,
                'files' => $this->recordFiles($kayit),
                'edit_url' => $editType ? "/kayit-form/{$editType}/{$kayit->id}" : null,
            ],
        ]);
    }
}
