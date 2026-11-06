<?php

namespace App\Http\Middleware;

use App\Support\SchoolContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('web');

        if ($admin) {
            $context = app(SchoolContext::class);
            $context->resolve($admin);

            View::share('schoolContext', $context);
            View::share('activeSchool', $context->school());
            View::share('schoolOptions', $context->availableSchools());
            View::share('foundationOptions', $context->availableFoundations());
        }

        return $next($request);
    }
}
