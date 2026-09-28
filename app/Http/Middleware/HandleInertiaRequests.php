<?php

namespace App\Http\Middleware;

use App\Models\Sakin;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $resident = $request->session()->has('resident_id')
            ? Sakin::query()
                ->whereKey($request->session()->get('resident_id'))
                ->where('bina_id', $request->session()->get('resident_bina_id'))
                ->where('is_active', true)
                ->first(['name', 'lastname', 'door_no'])
            : null;

        return array_merge(parent::share($request), [
            'userType' => $request->session()->has('resident_id')
                ? 'resident'
                : ($request->user() ? 'manager' : 'guest'),
            'resident' => $resident ? [
                'name' => trim($resident->name . ' ' . $resident->lastname),
                'door_no' => $resident->door_no,
            ] : null,
            'auth' => [
                'user' => $request->user(),
            ],
            'selected_bina' => $request->session()->get('selected_bina'),
            'app' => [
                'company' => config('constants.company'),
                'title' => config('constants.app.title'),
                'copyright' => config('constants.app.copyright'),
                'version' => config('constants.app.version'),
            ],
        ]);
    }
}
