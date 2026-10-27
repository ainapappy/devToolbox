<?php

namespace App\Mails;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

/**
 * Email de code 2FA par e-mail (maquette : mails/two-factor-code.html).
 * Code à 6 chiffres à usage unique, expiration 10 minutes.
 * Repli de l'application TOTP — jamais un remplacement.
 */
class TwoFactorCodeMail extends DevToolboxMail
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        public readonly string $code,
    ) {
        parent::__construct($user);
    }

    /**
     * Sujet de l'email.
     */
    protected function mailSubject(): string
    {
        return 'Votre code de vérification — DevToolbox';
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.two-factor-code',
            with: [
                'subject' => $this->mailSubject(),
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'code' => $this->code,
            ],
        );
    }
}
