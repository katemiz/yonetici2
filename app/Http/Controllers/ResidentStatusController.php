<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\Kayit;
use App\Models\Sakin;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResidentStatusController extends Controller
{
    private const TYPES = [
        'alacaklar' => 'alacak',
        'gelirler' => 'gelir',
        'giderler' => 'gider',
    ];

    public function show(Request $request, string $section)
    {
        $building = $this->buildingForResident($request);

        if ($section === 'ozet') {
            $totals = Kayit::query()
                ->where('bina_id', $building->id)
                ->selectRaw("SUM(CASE WHEN tur = 'gelir' THEN tutar ELSE 0 END) AS gelir")
                ->selectRaw("SUM(CASE WHEN tur = 'gider' THEN tutar ELSE 0 END) AS gider")
                ->selectRaw("SUM(CASE WHEN tur = 'alacak' THEN tutar ELSE 0 END) AS alacak")
                ->selectRaw("SUM(CASE WHEN tur = 'verecek' THEN tutar ELSE 0 END) AS verecek")
                ->first();

            $gelir = (float) ($totals->gelir ?? 0);
            $gider = (float) ($totals->gider ?? 0);

            return Inertia::render('Dashboard', [
                'bina' => [
                    'name' => $building->name,
                    'pbirimi' => $building->pbirimi,
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

        abort_unless(isset(self::TYPES[$section]), 404);

        $type = self::TYPES[$section];
        if (in_array($section, ['alacaklar', 'gelirler'], true)) {
            $resident = $this->residentForRequest($request, $building);
            $search = trim((string) $request->query('search', ''));
            $query = Kayit::query()
                ->with(['sakin', 'dosyalar'])
                ->where('kayitlar.bina_id', $building->id)
                ->where('kayitlar.tur', $type)
                ->leftJoin('sakinler', 'kayitlar.sakin_id', '=', 'sakinler.id')
                ->select('kayitlar.*')
                ->when($section === 'alacaklar', fn ($query) => $query->orderBy('sakinler.door_no'))
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
                    'amount' => $this->formatAmount((float) $kayit->tutar),
                    'created_at' => $kayit->created_at?->format('d-m-Y H:i:s'),
                    'can_view_receipt' => $kayit->sakin_id === $resident->id,
                ]);

            return Inertia::render($section === 'alacaklar' ? 'Alacaklar' : 'Gelirler', [
                'bina' => [
                    'name' => $building->name,
                    'pbirimi' => $building->pbirimi,
                ],
                'records' => $records,
                'search' => $search,
            ]);
        }

        if ($section === 'giderler') {
            $search = trim((string) $request->query('search', ''));
            $query = Kayit::query()
                ->with('dosyalar')
                ->where('bina_id', $building->id)
                ->where('tur', $type)
                ->orderByDesc('created_at');

            if ($search !== '') {
                $query->where(function ($query) use ($search) {
                    $query->where('aciklama', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }

            $records = $query
                ->paginate(config('constants.table.no_of_results'))
                ->withQueryString()
                ->through(fn (Kayit $kayit) => [
                    'id' => $kayit->id,
                    'description' => $kayit->aciklama,
                    'amount' => $this->formatAmount((float) $kayit->tutar),
                    'due_date' => $kayit->son_odeme,
                ]);

            return Inertia::render('Giderler', [
                'bina' => [
                    'name' => $building->name,
                    'pbirimi' => $building->pbirimi,
                ],
                'records' => $records,
                'search' => $search,
            ]);
        }

        abort(404);
    }

    private function buildingForResident(Request $request): Bina
    {
        $resident = $this->residentForRequest($request);
        $building = Bina::findOrFail($request->session()->get('resident_bina_id'));

        abort_unless($resident->bina_id === $building->id, 403);

        return $building;
    }

    private function residentForRequest(Request $request, ?Bina $building = null): Sakin
    {
        $resident = Sakin::query()
            ->whereKey($request->session()->get('resident_id'))
            ->where('is_active', true)
            ->firstOrFail();

        if ($building) {
            abort_unless($resident->bina_id === $building->id, 403);
        }

        return $resident;
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, ',', ' ');
    }
}
