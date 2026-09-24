<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResidentAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('resident_id')) {
            return redirect()->route('resident.login');
        }

        return $next($request);
    }
}
