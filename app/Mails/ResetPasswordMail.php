<?php

namespace App\Mails;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

/**
 * Email de réinitialisation de mot de passe (maquette : mails/reset-password.html).
 * Lien signé à usage unique, valable 60 minutes.
 */
class ResetPasswordMail extends DevToolboxMail
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        public readonly string $url,
    ) {
        parent::__construct($user);
    }

    /**
     * Sujet de l'email.
     */
    protected function mailSubject(): string
    {
        return 'Réinitialisation de votre mot de passe — DevToolbox';
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.reset-password',
            with: [
                'subject' => $this->mailSubject(),
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'url' => $this->url,
            ],
        );
    }
}
