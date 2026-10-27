<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\TwoFactorEmailCodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tools\MarkdownController;
use App\Http\Controllers\Tools\MockApiController;
use App\Http\Controllers\Tools\PasteController;
use App\Http\Controllers\Tools\PlaceholderController;
use App\Http\Controllers\Tools\UrlShortenerController;
use App\Mails\NewLoginMail;
use App\Mails\PasswordChangedMail;
use App\Mails\ResetPasswordMail;
use App\Mails\TwoFactorCodeMail;
use App\Mails\VerifyEmailMail;
use App\Mails\WelcomeMail;
use App\Models\User;
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
// Repli GET sur /reveal (refresh, partage d'URL) : retour à l'avertissement
// au lieu d'une MethodNotAllowedHttpException.
Route::get('p/{hash}/reveal', fn (string $hash) => redirect()->route('pastes.show', $hash))
    ->name('pastes.reveal.fallback');
Route::post('p/{hash}/reveal', [PasteController::class, 'reveal'])
    ->middleware('throttle:30,1')->name('pastes.reveal');

// Redirection des liens courts (cache + tracking asynchrone).
Route::get('s/{code}', [UrlShortenerController::class, 'redirect'])->name('urls.redirect');

// Code 2FA par e-mail (repli du TOTP — Phase 16) : envoi throttlé.
Route::post('two-factor/email-code', [TwoFactorEmailCodeController::class, 'store'])
    ->middleware('throttle:1,1')
    ->name('two-factor.email-code');

// Prévisualisation des emails transactionnels (dev uniquement — Phase 15).
if (app()->isLocal()) {
    Route::get('dev/mails', function () {
        $types = [
            'verify-email' => 'Vérification d\'adresse',
            'welcome' => 'Bienvenue',
            'reset-password' => 'Réinitialisation de mot de passe',
            'two-factor-code' => 'Code 2FA',
            'new-login' => 'Nouvelle connexion',
            'password-changed' => 'Mot de passe modifié',
        ];

        $links = implode('', array_map(
            fn ($type, $label) => '<li><a href="/dev/mails/'.$type.'">'.$label.'</a></li>',
            array_keys($types),
            $types,
        ));

        return response('<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Previews emails — DevToolbox</title></head><body style="font-family:sans-serif;max-width:600px;margin:40px auto;"><h1>Emails transactionnels — Auth</h1><ul style="font-size:18px;line-height:2">'.$links.'</ul></body></html>');
    })->name('dev.mails.index');

    Route::get('dev/mails/{type}', function (string $type) {
        $mail = match ($type) {
            'verify-email' => new VerifyEmailMail(
                User::factory()->make(['name' => 'Alex', 'email' => 'alex@exemple.dev']),
                'https://services.test/email/verify/1/fake-signed-token?expires='.now()->addMinutes(60)->getTimestamp(),
            ),
            'welcome' => new WelcomeMail(
                User::factory()->make(['name' => 'Alex', 'email' => 'alex@exemple.dev']),
                config('app.url'),
            ),
            'reset-password' => new ResetPasswordMail(
                User::factory()->make(['name' => 'Alex', 'email' => 'alex@exemple.dev']),
                'https://services.test/reset-password/fake-token?email=alex%40exemple.dev',
            ),
            'two-factor-code' => new TwoFactorCodeMail(
                User::factory()->make(['name' => 'Alex', 'email' => 'alex@exemple.dev']),
                '482913',
            ),
            'new-login' => new NewLoginMail(
                User::factory()->make(['name' => 'Alex', 'email' => 'alex@exemple.dev']),
                [
                    'device' => 'Chrome sur Windows 11',
                    'location' => 'Paris, France',
                    'ip' => '92.184.x.x',
                    'date' => now()->translatedFormat('d F Y \à H:i').' (UTC+'.now()->offsetHours.')',
                ],
                config('app.url'),
            ),
            'password-changed' => new PasswordChangedMail(
                User::factory()->make(['name' => 'Alex', 'email' => 'alex@exemple.dev']),
                now()->translatedFormat('d F Y \à H:i'),
            ),
            default => abort(404),
        };

        return response($mail->render());
    })->where('type', '[a-z-]+')->name('dev.mails.show');
}

require __DIR__.'/settings.php';
