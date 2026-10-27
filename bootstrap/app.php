<?php

use App\Http\Middleware\EnsureVerifiedIfAuthenticated;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Les invités restent acceptés sur les outils ; seuls les utilisateurs
        // connectés non vérifiés sont bloqués (Phase 12).
        $middleware->alias([
            'verified-if-auth' => EnsureVerifiedIfAuthenticated::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 419 sur la révélation d'un paste (session CSRF expirée pendant la
        // lecture de l'avertissement) : retour à la page d'avertissement avec
        // un jeton frais plutôt que l'erreur « Page Expired ».
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->routeIs('pastes.reveal')) {
                return redirect()->route('pastes.show', $request->route('hash'));
            }
        });
    })->create();
