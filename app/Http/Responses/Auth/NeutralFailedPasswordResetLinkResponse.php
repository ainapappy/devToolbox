<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Réponse d'échec de demande de lien de réinitialisation.
 * Anti-énumération : un compte inexistant ou un throttle du broker produit
 * exactement la même réponse neutre qu'un succès (Phase 11).
 */
class NeutralFailedPasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponseContract
{
    /**
     * Create a new response instance.
     */
    public function __construct(protected string $status) {}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        return back()->with('status', __('passwords.sent'));
    }
}
