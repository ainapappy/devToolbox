<?php

namespace App\Support;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate limiting transversal des emails de sécurité (Phase 16) :
 * max 5 emails auth / heure / destinataire, en plus des throttles par flux.
 */
class AuthMailThrottle
{
    /**
     * Maximum d'emails auth par heure et par utilisateur.
     */
    private const MAX_PER_HOUR = 5;

    /**
     * Tente d'envoyer un email de sécurité à l'utilisateur dans la limite
     * du quota. Retourne false si le quota est dépassé (silence, aucun
     * détail de sécurité journalisé).
     *
     * @param  Closure(): void  $send
     */
    public static function attempt(User $user, Closure $send): bool
    {
        $executed = RateLimiter::attempt(
            key: 'auth-mail:'.$user->getKey(),
            maxAttempts: self::MAX_PER_HOUR,
            callback: $send,
            decaySeconds: 3600,
        );

        if (! $executed) {
            Log::notice('Quota d\'emails auth atteint pour un utilisateur.', [
                'user_id' => $user->getKey(),
            ]);
        }

        return $executed;
    }
}
