<?php

namespace App\Http\Controllers;

use App\Models\Bedel;
use App\Models\Bina;
use App\Models\Kayit;
use App\Models\Okuma;
use App\Models\Sakin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SayacOkumaController extends Controller
{
    private function building(): Bina
    {
        return Bina::query()
            ->accessibleTo(Auth::user())
            ->findOrFail(session('bina_id'));
    }

    public function index(Request $request)
    {
        $building = $this->building();
        $meters = Bedel::query()
            ->where('bina_id', $building->id)
            ->where('tur', 'SAYAC')
            ->orderBy('title')
            ->get();
        $residents = Sakin::query()
            ->where('bina_id', $building->id)
            ->where('is_active', 1)
            ->orderBy('door_no')
            ->get();
        $readings = Okuma::query()
            ->where('bina_id', $building->id)
            ->whereIn('bedel_id', $meters->modelKeys())
            ->whereIn('sakin_id', $residents->modelKeys())
            ->orderByDesc('okuma_tarihi')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (Okuma $reading) => $reading->bedel_id . ':' . $reading->sakin_id);

        $selectedMeter = $meters->firstWhere('id', $request->integer('bedel'))?->id
            ?? $meters->firstWhere('id', $request->route('idBedel'))?->id
            ?? $meters->first()?->id;

        return Inertia::render('SayacOkuma', [
            'bina' => [
                'id' => $building->id,
                'name' => $building->name,
                'pbirimi' => $building->pbirimi,
            ],
            'meters' => $meters->map(fn (Bedel $meter) => [
                'id' => $meter->id,
                'title' => $meter->title,
                'unit' => $meter->unit,
                'rate' => (float) $meter->bedel,
                'residents' => $residents->map(function (Sakin $resident) use ($meter, $readings) {
                    $items = $readings->get($meter->id . ':' . $resident->id, collect());

                    return [
                        'id' => $resident->id,
                        'door_no' => $resident->door_no,
                        'name' => $resident->name,
                        'lastname' => $resident->lastname,
                        'readings' => $items->values()->map(fn (Okuma $reading) => [
                            'id' => $reading->id,
                            'value' => (float) $reading->okuma_degeri,
                            'date' => $reading->okuma_tarihi,
                            'formatted_date' => $reading->okuma_tarihi
                                ? Carbon::parse($reading->okuma_tarihi)->format('d M Y')
                                : '',
                            'note' => $reading->note,
                            'status' => $reading->status,
                            'record_id' => (int) $reading->kayit_id,
                        ])->all(),
                    ];
                })->all(),
            ])->values()->all(),
            'activeMeterId' => $selectedMeter,
            'oldInput' => [
                'bedel_id' => old('bedel_id'),
                'sakin_id' => old('sakin_id'),
                'okuma_degeri' => old('okuma_degeri'),
                'okuma_tarihi' => old('okuma_tarihi'),
                'note' => old('note'),
                'reading_id' => old('reading_id'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $building = $this->building();
        $data = $request->validate([
            'bedel_id' => ['required', 'integer'],
            'sakin_id' => ['required', 'integer'],
            'okuma_degeri' => ['required', 'numeric', 'min:0'],
            'okuma_tarihi' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $meter = Bedel::query()
            ->where('bina_id', $building->id)
            ->where('tur', 'SAYAC')
            ->findOrFail($data['bedel_id']);
        $resident = Sakin::query()
            ->where('bina_id', $building->id)
            ->where('is_active', 1)
            ->findOrFail($data['sakin_id']);

        Okuma::create([
            'user_id' => $building->user_id,
            'bina_id' => $building->id,
            'bedel_id' => $meter->id,
            'sakin_id' => $resident->id,
            'okuma_degeri' => $data['okuma_degeri'],
            'okuma_tarihi' => Carbon::parse($data['okuma_tarihi'])->toDateString(),
            'note' => $data['note'] ?? null,
            'status' => 'OKUNDU',
        ]);

        return redirect('/sayac-okuma?bedel=' . $meter->id);
    }

    public function update(Request $request, int $id)
    {
        $building = $this->building();
        $data = $request->validate([
            'okuma_degeri' => ['required', 'numeric', 'min:0'],
            'okuma_tarihi' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        $reading = Okuma::query()
            ->where('bina_id', $building->id)
            ->findOrFail($id);
        abort_unless($this->isUnchargedReading($reading), 403);

        $reading->update([
            'okuma_degeri' => $data['okuma_degeri'],
            'okuma_tarihi' => Carbon::parse($data['okuma_tarihi'])->toDateString(),
            'note' => $data['note'] ?? null,
        ]);

        return redirect('/sayac-okuma?bedel=' . $reading->bedel_id);
    }

    public function charge(int $id)
    {
        $building = $this->building();
        $reading = Okuma::query()
            ->where('bina_id', $building->id)
            ->findOrFail($id);

        abort_unless($this->isUnchargedReading($reading), 403);

        $meter = Bedel::query()
            ->where('bina_id', $building->id)
            ->where('tur', 'SAYAC')
            ->findOrFail($reading->bedel_id);

        DB::transaction(function () use ($building, $meter, $reading) {
            $reading = Okuma::query()
                ->where('bina_id', $building->id)
                ->lockForUpdate()
                ->findOrFail($reading->id);
            abort_unless($this->isUnchargedReading($reading), 403);

            $previousReading = Okuma::query()
                ->where('bina_id', $building->id)
                ->where('bedel_id', $meter->id)
                ->where('sakin_id', $reading->sakin_id)
                ->where('id', '!=', $reading->id)
                ->where(function ($query) use ($reading) {
                    $query->where('okuma_tarihi', '<', $reading->okuma_tarihi)
                        ->orWhere(function ($query) use ($reading) {
                            $query->where('okuma_tarihi', $reading->okuma_tarihi)
                                ->where('id', '<', $reading->id);
                        });
                })
                ->orderByDesc('okuma_tarihi')
                ->orderByDesc('id')
                ->first();

            $firstValue = (float) ($previousReading?->okuma_degeri ?? 0);
            $lastValue = (float) $reading->okuma_degeri;
            $amount = abs($lastValue - $firstValue) * (float) $meter->bedel;
            $record = Kayit::create([
                'user_id' => $building->user_id,
                'bina_id' => $building->id,
                'sakin_id' => $reading->sakin_id,
                'tur' => 'alacak',
                'aciklama' => $meter->title,
                'donem' => now()->format('M Y'),
                'son_odeme' => '',
                'dokum' => json_encode([
                    'son_okuma' => $lastValue,
                    'ilk_okuma' => $firstValue,
                    'birim_bedel' => (float) $meter->bedel,
                    'unit' => $meter->unit,
                ], JSON_THROW_ON_ERROR),
                'tutar' => $amount,
                'status' => 'KAYIT',
            ]);

            $reading->update([
                'kayit_id' => $record->id,
                'status' => 'FATURALANDI',
            ]);
        });

        return redirect('/sayac-okuma?bedel=' . $meter->id);
    }

    private function isUnchargedReading(Okuma $reading): bool
    {
        return (int) $reading->kayit_id === 0 && $reading->status === 'OKUNDU';
    }
}
