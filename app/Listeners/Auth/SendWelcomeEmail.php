<?php

namespace App\Listeners\Auth;

use App\Mails\WelcomeMail;
use App\Models\User;
use App\Support\AuthMailThrottle;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoie l'email de bienvenue après vérification effective de l'adresse
 * (pas à l'inscription — Phase 16). Idempotent : jamais deux fois.
 */
class SendWelcomeEmail
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        // Idempotence : un drapeau permanent évite tout doublon même si
        // l'événement était rejoué (le cache n'est pas le seul garde-fou :
        // l'événement Verified n'est diffusé qu'une fois, à la vérification).
        $flag = 'welcome-sent:'.$user->getKey();

        if (Cache::get($flag)) {
            return;
        }

        Cache::forever($flag, true);

        AuthMailThrottle::attempt($user, function () use ($user): void {
            Mail::to($user->email)->send(new WelcomeMail($user, route('home')));

            Log::info('Email de bienvenue envoyé.', ['user_id' => $user->getKey()]);
        });
    }
}
