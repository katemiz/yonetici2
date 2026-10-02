<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActiveManager
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(Auth::check() && Auth::user()->is_active, 403);

        return $next($request);
    }
}
