<?php

namespace App\Notifications\Auth;

use App\Mails\VerifyEmailMail;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

/**
 * Notification de vérification d'adresse e-mail branchée sur le Mailable
 * DevToolbox (markdown mails/verify-email — Phase 16). Le lien reste signé
 * et expirant.
 */
class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): VerifyEmailMail
    {
        return new VerifyEmailMail($notifiable, $this->verificationUrl($notifiable));
    }
}
