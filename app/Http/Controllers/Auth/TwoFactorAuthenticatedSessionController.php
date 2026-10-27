<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Support\TrustedDevice;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController as BaseTwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;
use Laravel\Fortify\Http\Responses\FailedTwoFactorLoginResponse;

/**
 * Défi 2FA : identique à Fortify, plus la gestion de l'appareil de confiance
 * (case « Se souvenir de cet appareil pendant 30 jours » — Phase 12).
 */
class TwoFactorAuthenticatedSessionController extends BaseTwoFactorAuthenticatedSessionController
{
    /**
     * Attempt to authenticate a new session using the two factor authentication code.
     *
     * @return mixed
     */
    public function store(TwoFactorLoginRequest $request)
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);
        } elseif (! $request->hasValidCode()
            && ! TwoFactorEmailCodeController::verifyAndConsume($request, (string) $request->input('code'))) {
            event(new TwoFactorAuthenticationFailed($user));

            return app(FailedTwoFactorLoginResponse::class)->toResponse($request);
        }

        event(new ValidTwoFactorAuthenticationCodeProvided($user));

        $this->guard->login($user, $request->remember());

        $request->session()->regenerate();

        $this->trustDeviceIfRequested($request, $user);

        return app(TwoFactorLoginResponse::class)->toResponse($request);
    }

    /**
     * Pose le cookie « appareil de confiance » si la case est cochée.
     *
     * @param  TwoFactorLoginRequest  $request
     */
    private function trustDeviceIfRequested(Request $request, User $user): void
    {
        if (! $request->boolean('trust') || ! $user->two_factor_secret) {
            return;
        }

        cookie()->queue(
            cookie(
                TrustedDevice::cookieName(),
                TrustedDevice::token($user),
                TrustedDevice::lifetimeMinutes(),
            ),
        );
    }
}
