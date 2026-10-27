<?php

namespace App\Mails;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

/**
 * Email de bienvenue après vérification (maquette : mails/welcome.html).
 * Grille des outils + astuce 2FA.
 */
class WelcomeMail extends DevToolboxMail
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
        return 'Bienvenue à bord — votre compte DevToolbox est actif';
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.welcome',
            with: [
                'subject' => $this->mailSubject(),
                'userName' => $this->user->name,
                'userEmail' => $this->user->email,
                'url' => $this->url,
                'tools' => [
                    ['name' => 'Secure Paste', 'description' => 'Partagez des secrets à lecture unique.'],
                    ['name' => 'URL Shortener', 'description' => 'Raccourcissez et suivez vos liens.'],
                    ['name' => 'JSON Mock API', 'description' => 'Prototypez vos APIs en secondes.'],
                    ['name' => 'Markdown Previewer', 'description' => 'Rendez vos documents lisibles et partageables.'],
                    ['name' => 'SVG Placeholder', 'description' => 'Générez des images placeholder à la volée.'],
                ],
            ],
        );
    }
}
