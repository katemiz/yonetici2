<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\Kayit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DurumController extends Controller
{
    public function summary(): Response|RedirectResponse
    {
        $bina = $this->activeBina();

        if (!$bina) {
            return redirect()->route('binalar');
        }

        $totals = Kayit::query()
            ->where('bina_id', $bina->id)
            ->selectRaw("SUM(CASE WHEN tur = 'gelir' THEN tutar ELSE 0 END) AS gelir")
            ->selectRaw("SUM(CASE WHEN tur = 'gider' THEN tutar ELSE 0 END) AS gider")
            ->selectRaw("SUM(CASE WHEN tur = 'alacak' THEN tutar ELSE 0 END) AS alacak")
            ->selectRaw("SUM(CASE WHEN tur = 'verecek' THEN tutar ELSE 0 END) AS verecek")
            ->first();

        $gelir = (float) ($totals->gelir ?? 0);
        $gider = (float) ($totals->gider ?? 0);

        return Inertia::render('Durum', [
            'bina' => [
                'name' => $bina->name,
                'pbirimi' => $bina->pbirimi,
            ],
            'totals' => [
                'gelir' => $this->formatAmount($gelir),
                'gider' => $this->formatAmount($gider),
                'nakit' => $this->formatAmount($gelir - $gider),
                'alacak' => $this->formatAmount((float) ($totals->alacak ?? 0)),
                'verecek' => $this->formatAmount((float) ($totals->verecek ?? 0)),
            ],
        ]);
    }

    public function receivables(Request $request): Response|RedirectResponse
    {
        $bina = $this->activeBina();

        if (!$bina) {
            return redirect()->route('binalar');
        }

        $search = trim((string) $request->query('search', ''));
        $query = Kayit::query()
            ->with(['sakin', 'dosyalar'])
            ->where('kayitlar.bina_id', $bina->id)
            ->where('kayitlar.tur', 'alacak')
            ->leftJoin('sakinler', 'kayitlar.sakin_id', '=', 'sakinler.id')
            ->select('kayitlar.*')
            ->orderBy('sakinler.door_no')
            ->orderByDesc('kayitlar.created_at');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('kayitlar.aciklama', 'like', "%{$search}%")
                    ->orWhere('kayitlar.remarks', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->paginate(config('constants.table.no_of_results'))
            ->withQueryString()
            ->through(fn (Kayit $kayit) => [
                'id' => $kayit->id,
                'door_no' => $kayit->sakin?->door_no,
                'resident_name' => trim(($kayit->sakin?->name ?? '') . ' ' . ($kayit->sakin?->lastname ?? '')),
                'description' => $kayit->aciklama,
                'amount' => number_format((float) $kayit->tutar, 2, ',', ' '),
                'created_at' => $kayit->created_at,
                'files' => $kayit->dosyalar->map(fn ($file) => [
                    'id' => $file->id,
                    'name' => $file->filename,
                ])->values(),
            ]);

        return Inertia::render('Alacaklar', [
            'bina' => [
                'name' => $bina->name,
                'pbirimi' => $bina->pbirimi,
            ],
            'records' => $records,
            'search' => $search,
        ]);
    }

    public function markReceivableReceived(int $id): RedirectResponse
    {
        $bina = $this->activeBina();

        if ($bina) {
            Kayit::query()
                ->whereKey($id)
                ->where('bina_id', $bina->id)
                ->where('tur', 'alacak')
                ->update(['tur' => 'gelir']);
        }

        return redirect()->route('durum.receivables');
    }

    public function incomes(Request $request): Response|RedirectResponse
    {
        $bina = $this->activeBina();

        if (!$bina) {
            return redirect()->route('binalar');
        }

        $search = trim((string) $request->query('search', ''));
        $query = Kayit::query()
            ->with(['sakin', 'dosyalar'])
            ->where('kayitlar.bina_id', $bina->id)
            ->where('kayitlar.tur', 'gelir')
            ->leftJoin('sakinler', 'kayitlar.sakin_id', '=', 'sakinler.id')
            ->select('kayitlar.*')
            ->orderByDesc('kayitlar.created_at');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('kayitlar.aciklama', 'like', "%{$search}%")
                    ->orWhere('kayitlar.remarks', 'like', "%{$search}%");
            });
        }

        $records = $query
            ->paginate(config('constants.table.no_of_results'))
            ->withQueryString()
            ->through(fn (Kayit $kayit) => [
                'id' => $kayit->id,
                'door_no' => $kayit->sakin?->door_no,
                'resident_name' => trim(($kayit->sakin?->name ?? '') . ' ' . ($kayit->sakin?->lastname ?? '')),
                'description' => $kayit->aciklama,
                'amount' => number_format((float) $kayit->tutar, 2, ',', ' '),
                'created_at' => $kayit->created_at,
                'files' => $kayit->dosyalar->map(fn ($file) => [
                    'id' => $file->id,
                    'name' => $file->filename,
                ])->values(),
            ]);

        return Inertia::render('Gelirler', [
            'bina' => [
                'name' => $bina->name,
                'pbirimi' => $bina->pbirimi,
            ],
            'records' => $records,
            'search' => $search,
        ]);
    }

    private function activeBina(): ?Bina
    {
        return Bina::query()
            ->where('user_id', Auth::id())
            ->whereKey(session('bina_id'))
            ->first();
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, ',', ' ');
    }
}
