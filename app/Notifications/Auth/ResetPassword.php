<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification de réinitialisation de mot de passe aux couleurs DevToolbox.
 * Le lien est signé et son expiration est affichée dans l'e-mail (Phase 11).
 */
class ResetPassword extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  string  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url)
    {
        $expire = (int) config(
            'auth.passwords.'.config('auth.defaults.passwords').'.expire',
            60,
        );

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->markdown('emails.reset-password', [
                'url' => $url,
                'expire' => $expire,
            ]);
    }
}
