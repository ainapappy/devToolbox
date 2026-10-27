<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\TrustedDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

/**
 * Connexion sociale GitHub & Google (Phase 13).
 *
 * Fusion par e-mail : liaison seulement si l'e-mail local est **vérifié**
 * (anti-takeover). Un compte local non vérifié avec le même e-mail → refus
 * et orientation vers le flux classique.
 */
class SocialAuthController extends Controller
{
    /**
     * Providers autorisés : whitelist stricte (pas de réflexion sur la config).
     */
    private const PROVIDERS = ['github', 'google'];

    /**
     * Redirige vers le provider (connexion invité ou liaison connecté).
     */
    public function redirect(Request $request, string $provider): mixed
    {
        $this->authorizeProvider($provider);

        // Mode liaison (utilisateur connecté) : mémorisé pour le callback.
        if ($request->user()) {
            session(['social.link' => true]);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Callback OAuth : connexion, fusion ou création.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $this->authorizeProvider($provider);

        // Retour annulé côté provider (l'utilisateur a refusé).
        if ($request->has('error')) {
            return $this->cancelled();
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException) {
            return $this->cancelled();
        }

        $linking = (bool) session()->pull('social.link', false);

        // Mode liaison : utilisateur connecté qui rattache un provider.
        if ($request->user() && $linking) {
            return $this->linkAuthenticated($request, $provider, $socialUser);
        }

        // 1. Provider déjà lié → connexion directe.
        $account = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', (string) $socialUser->getId())
            ->first();

        if ($account) {
            return $this->login($request, $account->user);
        }

        // 2. Compte local avec le même e-mail vérifié → liaison + connexion.
        $user = User::query()->where('email', $socialUser->getEmail())->first();

        if ($user) {
            if (! $user->hasVerifiedEmail()) {
                // Anti-takeover : compte existant non vérifié → flux classique.
                return redirect()->route('login')->with(
                    'status',
                    __('Un compte existe déjà avec cet e-mail mais il n\'est pas vérifié. Connectez-vous avec votre mot de passe pour le vérifier.'),
                );
            }

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => (string) $socialUser->getId(),
                'provider_email' => $socialUser->getEmail(),
            ]);

            return $this->login($request, $user);
        }

        // 3. Aucun compte → inscription (e-mail considéré vérifié chez
        //    GitHub/Google) puis connexion.
        $user = User::create([
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Utilisateur',
            'email' => $socialUser->getEmail(),
            // Mot de passe aléatoire : la connexion sociale ne l'utilise pas.
            'password' => Hash::make(Str::random(32)),
        ]);
        // E-mail considéré vérifié chez GitHub/Google (non fillable).
        $user->forceFill(['email_verified_at' => now()])->save();

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => (string) $socialUser->getId(),
            'provider_email' => $socialUser->getEmail(),
        ]);

        return $this->login($request, $user);
    }

    /**
     * Déconnecte un provider du compte connecté.
     */
    public function unlink(Request $request, string $provider): RedirectResponse
    {
        $this->authorizeProvider($provider);

        $request->user()->socialAccounts()
            ->where('provider', $provider)
            ->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Connexion :provider déliée.', ['provider' => $provider]),
        ]);

        return back();
    }

    /**
     * Liaison pour un utilisateur déjà connecté (page profil).
     */
    private function linkAuthenticated(Request $request, string $provider, mixed $socialUser): RedirectResponse
    {
        $taken = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', (string) $socialUser->getId())
            ->exists();

        if ($taken) {
            return redirect()->route('security.edit')->with(
                'status',
                __('Ce compte :provider est déjà rattaché à un autre utilisateur.', ['provider' => $provider]),
            );
        }

        $request->user()->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => (string) $socialUser->getId(),
            'provider_email' => $socialUser->getEmail(),
        ]);

        return redirect()->route('security.edit');
    }

    /**
     * Connexion (avec court-circuit appareil de confiance et défi 2FA).
     */
    private function login(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);

        $request->session()->regenerate();

        // 2FA activée : même comportement que le flux classique.
        if ($user->hasEnabledTwoFactorAuthentication()) {
            // Appareil de confiance : le défi est court-circuité.
            if (TrustedDevice::verify($request, $user)) {
                return redirect()->intended(config('fortify.home'));
            }

            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => false,
            ]);

            return redirect()->route('two-factor.login');
        }

        return redirect()->intended(config('fortify.home'));
    }

    /**
     * Retour annulé (refus OAuth ou état invalide) : message neutre, pas d'erreur brute.
     */
    private function cancelled(): RedirectResponse
    {
        return redirect()->route('login')->with(
            'status',
            __('Connexion sociale annulée. Vous pouvez réessayer ou utiliser votre mot de passe.'),
        );
    }

    /**
     * Refuse tout provider hors whitelist.
     */
    private function authorizeProvider(string $provider): void
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
    }
}
