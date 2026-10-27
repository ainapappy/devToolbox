<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Controllers\Auth\TwoFactorAuthenticatedSessionController;
use App\Http\Responses\Auth\NeutralFailedPasswordResetLinkResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController as BaseTwoFactorAuthenticatedSessionController;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Anti-énumération : tout échec de demande de lien (compte inexistant,
        // throttle du broker) répond comme un succès (Phase 11).
        $this->app->singleton(
            FailedPasswordResetLinkRequestResponse::class,
            NeutralFailedPasswordResetLinkResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        $this->configureTrustedDeviceFlow();
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure le flux « appareil de confiance » du défi 2FA (Phase 12) :
     * contrôleur qui pose le cookie + étape de pipeline qui court-circuite
     * le défi sur un appareil déjà approuvé.
     */
    private function configureTrustedDeviceFlow(): void
    {
        $this->app->bind(
            BaseTwoFactorAuthenticatedSessionController::class,
            TwoFactorAuthenticatedSessionController::class,
        );

        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
                Features::enabled(Features::twoFactorAuthentication())
                    ? RedirectIfTwoFactorAuthenticatable::class
                    : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/verify-email', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => Inertia::render('auth/register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::twoFactorChallengeView(fn (Request $request) => Inertia::render('auth/two-factor-challenge', [
            'emailStatus' => $request->session()->get('twoFactorEmailStatus'),
        ]));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/confirm-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        // Demande de lien de réinitialisation : 3 requêtes / minute / IP (Phase 11).
        RateLimiter::for('reset-link', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        // Renvoi de l'e-mail de vérification : 1 / minute / utilisateur —
        // double le cooldown 60 s de l'interface (Phase 12).
        RateLimiter::for('verification', function (Request $request) {
            return Limit::perMinute(1)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)->by(
                ($request->input('credential.id') ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });

        // Les routes Fortify sont déjà enregistrées à ce stade (provider du
        // package booté avant ceux de l'application) : on y accroche le limiter.
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if ($route->getName() === 'password.email') {
                $route->middleware('throttle:reset-link');
            }
        }
    }
}
