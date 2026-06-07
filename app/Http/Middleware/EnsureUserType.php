<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    /**
     * Autorise l'accès uniquement aux utilisateurs du type attendu.
     *
     * Usage : ->middleware('type:admin') ou ->middleware('type:client')
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        if (! $request->user() || $request->user()->type !== $type) {
            abort(403);
        }

        return $next($request);
    }
}
