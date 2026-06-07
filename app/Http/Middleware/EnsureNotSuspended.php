<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotSuspended
{
    /**
     * Déconnecte immédiatement un compte suspendu, même session déjà ouverte.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isSuspended()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => trans('Votre compte a été suspendu. Contactez un administrateur.'),
            ]);
        }

        return $next($request);
    }
}
