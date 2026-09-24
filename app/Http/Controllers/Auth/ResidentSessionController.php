<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Bina;
use App\Models\Sakin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ResidentSessionController extends Controller
{
    public function create()
    {
        return view('auth.resident-login');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'building_code' => ['required', 'string', 'max:64'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        $key = 'resident-login|' . $request->ip() . '|' . $phone;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'phone' => 'Çok fazla başarısız deneme yapıldı. Lütfen daha sonra tekrar deneyiniz.',
            ]);
        }

        $resident = null;
        $building = null;

        foreach (Sakin::where('phone', $phone)->where('is_active', true)->get() as $candidate) {
            $candidateBuilding = Bina::find($candidate->bina_id);

            if ($candidateBuilding && $candidateBuilding->resident_access_code &&
                Hash::check($validated['building_code'], $candidateBuilding->resident_access_code)) {
                $resident = $candidate;
                $building = $candidateBuilding;
                break;
            }
        }

        if (!$resident || !$building) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'phone' => 'Telefon numarası veya bina giriş kodu geçersiz.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put([
            'resident_id' => $resident->id,
            'resident_bina_id' => $building->id,
            'selected_bina' => $building->name,
        ]);

        return redirect()->route('resident.dashboard');
    }

    public function destroy(Request $request)
    {
        $request->session()->forget(['resident_id', 'resident_bina_id']);
        $request->session()->regenerateToken();

        return redirect()->route('resident.login');
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', trim($phone));
    }
}
