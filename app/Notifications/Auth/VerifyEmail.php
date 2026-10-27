<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification de vérification d'adresse e-mail aux couleurs DevToolbox
 * (markdown mail — Phase 12). Le lien reste signé et expirant.
 */
class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Vérifiez votre adresse e-mail')
            ->markdown('emails.verify-email', [
                'url' => $verificationUrl,
                'expire' => (int) ceil(
                    config('auth.verification.expire', 60) / 60,
                ),
            ]);
    }
}
