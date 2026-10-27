<?php

namespace App\Notifications\Auth;

use App\Mails\ResetPasswordMail;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

/**
 * Notification de réinitialisation de mot de passe branchée sur le Mailable
 * DevToolbox (markdown mails/reset-password — Phase 16).
 * Lien signé à usage unique, expiration affichée.
 */
class ResetPassword extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): ResetPasswordMail
    {
        return new ResetPasswordMail($notifiable, $this->resetUrl($notifiable));
    }
}
