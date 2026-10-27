<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;

/**
 * « Appareil de confiance » pour le défi 2FA (Phase 12).
 * Cookie (chiffré par le middleware) valable 30 jours : identifiant
 * utilisateur + empreinte du secret TOTP — le cookie s'invalide si la 2FA
 * est réactivée avec un nouveau secret.
 */
class TrustedDevice
{
    /**
     * @return non-empty-string
     */
    public static function cookieName(): string
    {
        return 'dtb_trusted_device';
    }

    /**
     * Durée de validité en minutes (30 jours).
     */
    public static function lifetimeMinutes(): int
    {
        return 60 * 24 * 30;
    }

    /**
     * Jeton de confiance pour l'utilisateur donné : non forgeable sans
     * connaître le secret TOTP.
     * L'empreinte se calcule sur le secret déchiffré — le chiffré change à
     * chaque enregistrement (IV aléatoire).
     */
    public static function token(User $user): string
    {
        $secret = $user->two_factor_secret
            ? Fortify::currentEncrypter()->decrypt($user->two_factor_secret)
            : '';

        return $user->getKey().'|'.hash('sha256', (string) $secret);
    }

    /**
     * Vérifie que le cookie de l'appareil correspond à l'utilisateur.
     * (Le middleware déchiffre déjà le cookie avant l'accès.)
     */
    public static function verify(Request $request, User $user): bool
    {
        $cookie = $request->cookie(self::cookieName());

        if (! is_string($cookie) || $cookie === '' || ! $user->two_factor_secret) {
            return false;
        }

        return hash_equals(self::token($user), $cookie);
    }
}
