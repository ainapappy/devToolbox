<?php

namespace App\Mails;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

/**
 * Email de confirmation de changement de mot de passe
 * (maquette : mails/password-changed.html).
 */
class PasswordChangedMail extends DevToolboxMail
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        public readonly ?string $at = null,
        public readonly string $url = '',
    ) {
        parent::__construct($user);
    }

    /**
     * Sujet de l'email.
     */
    protected function mailSubject(): string
    {
        return 'Votre mot de passe a été mis à jour — DevToolbox';
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.password-changed',
            with: [
                'subject' => $this->mailSubject(),
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'at' => $this->at,
                'url' => $this->url !== '' ? $this->url : url('/'),
            ],
        );
    }
}
