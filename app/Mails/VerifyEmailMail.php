<?php

namespace App\Mails;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

/**
 * Email de vérification d'adresse (maquette : mails/verify-email.html).
 * URL signée valable 60 minutes.
 */
class VerifyEmailMail extends DevToolboxMail
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
        return 'Confirmez votre adresse email — DevToolbox';
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.verify-email',
            with: [
                'subject' => $this->mailSubject(),
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'url' => $this->url,
            ],
        );
    }
}
