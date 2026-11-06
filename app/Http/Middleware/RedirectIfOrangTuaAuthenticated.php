<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOrangTuaAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('orangtua')->check()) {
            return redirect()->route('orangtua.dashboard');
        }

        return $next($request);
    }
}
