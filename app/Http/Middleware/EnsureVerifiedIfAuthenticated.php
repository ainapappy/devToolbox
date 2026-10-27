<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque uniquement les utilisateurs connectés dont l'adresse n'est pas
 * vérifiée (Phase 12). Les invités passent : les outils restent accessibles
 * de façon anonyme — cf. .knowledge/prompts/001.md.
 */
class EnsureVerifiedIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? abort(403, 'Votre adresse e-mail n\'est pas vérifiée.')
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
