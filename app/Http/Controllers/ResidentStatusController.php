<?php

namespace App\Http\Controllers;

use App\Models\Bina;
use App\Models\Kayit;
use App\Models\Sakin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResidentStatusController extends Controller
{
    private const TYPES = [
        'alacaklar' => ['label' => 'Alacaklar', 'type' => 'alacak'],
        'gelirler' => ['label' => 'Gelirler', 'type' => 'gelir'],
        'giderler' => ['label' => 'Giderler', 'type' => 'gider'],
    ];

    public function show(Request $request, string $section)
    {
        $building = $this->buildingForResident($request);

        if ($section === 'ozet') {
            return view('resident.status', [
                'section' => 'ozet',
                'sectionLabel' => 'Genel Durum',
                'building' => $building,
                'summary' => $this->summary($building->id),
                'records' => collect(),
            ]);
        }

        abort_unless(isset(self::TYPES[$section]), 404);

        $type = self::TYPES[$section];
        $records = Kayit::query()
            ->where('bina_id', $building->id)
            ->where('tur', $type['type'])
            ->with('sakin')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('resident.status', [
            'section' => $section,
            'sectionLabel' => $type['label'],
            'building' => $building,
            'summary' => null,
            'records' => $records,
        ]);
    }

    private function buildingForResident(Request $request): Bina
    {
        $resident = Sakin::findOrFail($request->session()->get('resident_id'));
        $building = Bina::findOrFail($request->session()->get('resident_bina_id'));

        abort_unless($resident->bina_id === $building->id && $resident->is_active, 403);

        return $building;
    }

    private function summary(int $buildingId): array
    {
        $totals = Kayit::query()
            ->where('bina_id', $buildingId)
            ->select('tur', DB::raw('SUM(tutar) as total'))
            ->groupBy('tur')
            ->pluck('total', 'tur');

        $income = (float) ($totals['gelir'] ?? 0);
        $expense = (float) ($totals['gider'] ?? 0);

        return [
            'gelirler' => $income,
            'giderler' => $expense,
            'alacaklar' => (float) ($totals['alacak'] ?? 0),
            'verecekler' => (float) ($totals['verecek'] ?? 0),
            'nakit' => $income - $expense,
        ];
    }
}
