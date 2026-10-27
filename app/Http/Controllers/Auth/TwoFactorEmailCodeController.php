<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mails\TwoFactorCodeMail;
use App\Models\User;
use App\Support\AuthMailThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Envoi du code 2FA par e-mail (Phase 16) — repli de l'application TOTP.
 * Code à 6 chiffres à usage unique : stocké hashé en cache, expiration
 * 10 minutes, throttle 1/minute par utilisateur.
 */
class TwoFactorEmailCodeController extends Controller
{
    /**
     * Durée de validité du code (minutes).
     */
    private const TTL_MINUTES = 10;

    /**
     * Envoie un code par e-mail à l'utilisateur en cours de défi 2FA.
     */
    public function store(Request $request): RedirectResponse
    {
        $loginId = $request->session()->get('login.id');

        if (! $loginId) {
            throw ValidationException::withMessages([
                'code' => [__('Aucun défi 2FA en cours. Connectez-vous à nouveau.')],
            ]);
        }

        $user = User::query()->whereKey($loginId)->firstOrFail();

        $sent = AuthMailThrottle::attempt($user, function () use ($request, $user): void {
            $code = (string) random_int(100000, 999999);

            // Hashé en cache : jamais de code en clair côté serveur stocké.
            Cache::put(
                $this->cacheKey($request),
                hash('sha256', $code),
                now()->addMinutes(self::TTL_MINUTES),
            );

            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
        });

        if (! $sent) {
            // Quota dépassé : message générique, pas de détail.
            return back()->with(
                'twoFactorEmailStatus',
                __('Si un email pouvait être envoyé, il arriverait dans quelques instants. Réessayez plus tard.'),
            );
        }

        Log::info('Code 2FA envoyé par email.', ['user_id' => $user->getKey()]);

        return back()->with(
            'twoFactorEmailStatus',
            __('Un code vient d\'être envoyé à votre adresse e-mail. Il est valable 10 minutes.'),
        );
    }

    /**
     * Vérifie et consomme le code e-mail fourni (usage unique).
     * Utilisé par le contrôleur de défi 2FA.
     */
    public static function verifyAndConsume(Request $request, string $code): bool
    {
        $key = (new self)->cacheKey($request);

        $expected = Cache::get($key);

        if (! is_string($expected)) {
            return false;
        }

        if (! hash_equals($expected, hash('sha256', $code))) {
            return false;
        }

        Cache::forget($key);

        return true;
    }

    /**
     * Clé de cache liée à la session de défi.
     */
    private function cacheKey(Request $request): string
    {
        return '2fa-email-code:'.$request->session()->get('login.id');
    }
}
