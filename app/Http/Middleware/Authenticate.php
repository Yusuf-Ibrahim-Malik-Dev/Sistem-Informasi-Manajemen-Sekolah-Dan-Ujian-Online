<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->is('peserta') || $request->is('peserta/*')) {
            return route('peserta.login');
        }

        if ($request->is('orangtua') || $request->is('orangtua/*')) {
            return route('orangtua.login');
        }

        return route('admin.login');
    }
}
