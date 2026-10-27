<?php

namespace App\Mails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable de base des emails transactionnels DevToolbox : `from` /
 * `reply-to` centralisés dans config/mail.php, file d'attente, sujet
 * partagé avec la vue (balise <title> du layout).
 */
abstract class DevToolboxMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Nombre d'essais avant abandon.
     */
    public int $tries = 3;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly User $user) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->user->email],
            replyTo: [new Address(
                config('mail.reply_to.address'),
                config('mail.reply_to.name'),
            )],
            subject: $this->mailSubject(),
        );
    }

    /**
     * Sujet de l'email (français, ton DevToolbox).
     */
    abstract protected function mailSubject(): string;
}
