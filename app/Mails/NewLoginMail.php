<?php

namespace App\Mails;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

/**
 * Email d'alerte de nouvelle connexion (maquette : mails/new-login.html).
 * Envoyé uniquement depuis un appareil inconnu (Phase 16).
 */
class NewLoginMail extends DevToolboxMail
{
    /**
     * Create a new message instance.
     *
     * @param  array{device: string, location: string, ip: string, date: string}  $context
     */
    public function __construct(
        User $user,
        public readonly array $context,
        public readonly string $url,
    ) {
        parent::__construct($user);
    }

    /**
     * Sujet de l'email.
     */
    protected function mailSubject(): string
    {
        return 'Nouvelle connexion à votre compte — DevToolbox';
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.new-login',
            with: [
                'subject' => $this->mailSubject(),
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'device' => $this->context['device'],
                'location' => $this->context['location'],
                'ip' => $this->context['ip'],
                'date' => $this->context['date'],
                'url' => $this->url,
            ],
        );
    }
}
