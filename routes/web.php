<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tools\MarkdownController;
use App\Http\Controllers\Tools\MockApiController;
use App\Http\Controllers\Tools\PasteController;
use App\Http\Controllers\Tools\PlaceholderController;
use App\Http\Controllers\Tools\UrlShortenerController;
use Illuminate\Support\Facades\Route;

// Connexion sociale GitHub & Google (Phase 13) — whitelist stricte dans le
// contrôleur ; callbacks throttlés par IP.
Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->where('provider', 'github|google')
    ->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->where('provider', 'github|google')
    ->middleware('throttle:10,1')
    ->name('social.callback');
Route::delete('auth/{provider}', [SocialAuthController::class, 'unlink'])
    ->where('provider', 'github|google')
    ->middleware(['auth', 'verified'])
    ->name('social.unlink');

// Démo des composants auth (temporaire — Phase 09, à retirer aux Phases 10→12).
Route::inertia('dev/auth-demo', 'auth/demo')->name('auth.demo');

// Front-office public : dashboard + navigation des outils.
// Les outils exigent une adresse vérifiée pour les utilisateurs connectés
// (les invités restent acceptés — décision documentée dans .knowledge/prompts/001.md).
Route::get('/', DashboardController::class)->name('home');
Route::get('/dashboard', DashboardController::class)->name('dashboard');

Route::prefix('tools')->name('tools.')->middleware('verified-if-auth')->group(function () {
    Route::get('paste', [PasteController::class, 'create'])->name('paste.index');
    Route::post('paste', [PasteController::class, 'store'])
        ->middleware('throttle:20,1')->name('paste.store');
    Route::get('mock-api', [MockApiController::class, 'create'])->name('mock-api.index');
    Route::post('mock-api', [MockApiController::class, 'store'])
        ->middleware('throttle:20,1')->name('mock-api.store');
    Route::get('shortener', [UrlShortenerController::class, 'index'])->name('shortener.index');
    Route::post('shortener', [UrlShortenerController::class, 'store'])
        ->middleware('throttle:20,1')->name('shortener.store');
    Route::get('shortener/stats', [UrlShortenerController::class, 'stats'])->name('shortener.stats');
    Route::get('markdown', [MarkdownController::class, 'index'])->name('markdown.index');
    Route::post('markdown', [MarkdownController::class, 'store'])
        ->middleware('throttle:10,1')->name('markdown.store');
    Route::get('markdown/{slug}', [MarkdownController::class, 'show'])->name('markdown.show');
    Route::get('placeholder', [PlaceholderController::class, 'index'])->name('placeholder.index');
});

// Génération d'images placeholder SVG (dimension contrainte par regex).
Route::get('placeholder/{size}', [PlaceholderController::class, 'show'])
    ->where('size', '[0-9]{1,4}x[0-9]{1,4}')
    ->middleware('throttle:60,1')
    ->name('placeholders.show');

// Endpoint public d'un mock JSON (cache write-through à la création).
Route::get('api/mock/{slug}', [MockApiController::class, 'serve'])->name('mocks.serve');

// Lecture publique d'un paste (avertissement puis révélation).
Route::get('p/{hash}', [PasteController::class, 'show'])->name('pastes.show');
Route::post('p/{hash}/reveal', [PasteController::class, 'reveal'])
    ->middleware('throttle:30,1')->name('pastes.reveal');

// Redirection des liens courts (cache + tracking asynchrone).
Route::get('s/{code}', [UrlShortenerController::class, 'redirect'])->name('urls.redirect');

require __DIR__.'/settings.php';
