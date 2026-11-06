<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next, string $kodeMenu = '', string $action = 'view'): Response
    {
        $admin = $request->user('web');

        if (! $admin) {
            return redirect()->route('admin.login');
        }

        if ($kodeMenu !== '' && ! $admin->canMenuAction($kodeMenu, $action)) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak memiliki akses untuk aksi tersebut.');
        }

        return $next($request);
    }
}
