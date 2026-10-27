<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\TrustedDevice;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as BaseRedirectIfTwoFactorAuthenticatable;
use Symfony\Component\HttpFoundation\Response;

/**
 * Étape du pipeline de connexion : redirige vers le défi 2FA, sauf si
 * l'appareil est de confiance (cookie 30 jours — Phase 12).
 */
class RedirectIfTwoFactorAuthenticatable extends BaseRedirectIfTwoFactorAuthenticatable
{
    /**
     * Get the two factor authentication enabled response.
     *
     * @param  Request  $request
     * @param  mixed  $user
     * @return Response
     */
    protected function twoFactorChallengeResponse($request, $user)
    {
        if ($user instanceof User && TrustedDevice::verify($request, $user)) {
            // Appareil de confiance : le défi est court-circuité.
            $this->guard->login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(config('fortify.home'));
        }

        return parent::twoFactorChallengeResponse($request, $user);
    }
}
