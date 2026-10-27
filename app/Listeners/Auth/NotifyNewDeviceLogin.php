<?php

namespace App\Listeners\Auth;

use App\Mails\NewLoginMail;
use App\Models\KnownDevice;
use App\Models\User;
use App\Support\AuthMailThrottle;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Alerte de nouvelle connexion (Phase 16) : n'envoie l'email que depuis un
 * appareil inconnu (empreinte IP + user-agent). Le tout premier appareil
 * est enregistré silencieusement — l'inscription a déjà son email de
 * bienvenue.
 */
class NotifyNewDeviceLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(protected Request $request) {}

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $fingerprint = hash('sha256', $this->request->ip().'|'.$this->request->userAgent());

        $known = KnownDevice::query()
            ->where('user_id', $user->getKey())
            ->where('fingerprint', $fingerprint)
            ->exists();

        if ($known) {
            return;
        }

        // Enregistre l'appareil avant l'envoi : un evenement rejoué ne
        // doit pas produire deux alertes.
        KnownDevice::create([
            'user_id' => $user->getKey(),
            'fingerprint' => $fingerprint,
            'device' => $this->describeDevice(),
            'first_seen_at' => now(),
        ]);

        // Premier appareil connu : pas d'alerte (l'email de bienvenue
        // vient d'être envoyé après vérification).
        $hasOtherDevices = KnownDevice::query()
            ->where('user_id', $user->getKey())
            ->where('fingerprint', '!=', $fingerprint)
            ->exists();

        if (! $hasOtherDevices) {
            return;
        }

        AuthMailThrottle::attempt($user, function () use ($user): void {
            Mail::to($user->email)->send(new NewLoginMail(
                $user,
                $this->loginContext(),
                route('security.edit'),
            ));

            Log::info('Alerte de nouvelle connexion envoyée.', ['user_id' => $user->getKey()]);
        });
    }

    /**
     * Description lisible de l'appareil (navigateur sur OS).
     */
    private function describeDevice(): string
    {
        $agent = (string) $this->request->userAgent();

        $browser = match (true) {
            stripos($agent, 'edg/') !== false => 'Edge',
            stripos($agent, 'opr/') !== false || stripos($agent, 'chrome') !== false => 'Chrome',
            stripos($agent, 'firefox') !== false => 'Firefox',
            stripos($agent, 'safari') !== false => 'Safari',
            default => 'Navigateur inconnu',
        };

        $os = match (true) {
            stripos($agent, 'windows') !== false => 'Windows',
            stripos($agent, 'android') !== false => 'Android',
            stripos($agent, 'iphone') !== false || stripos($agent, 'ipad') !== false => 'iOS',
            stripos($agent, 'mac') !== false => 'macOS',
            stripos($agent, 'linux') !== false => 'Linux',
            default => 'Système inconnu',
        };

        return $browser.' sur '.$os;
    }

    /**
     * Contexte de connexion pour l'email.
     *
     * @return array{device: string, location: string, ip: string, date: string}
     */
    private function loginContext(): array
    {
        return [
            'device' => $this->describeDevice(),
            // Pas de géolocalisation en local : la ville serait trompeuse.
            'location' => 'Non déterminée',
            'ip' => (string) $this->request->ip(),
            'date' => now()->translatedFormat('d F Y \à H:i').' (UTC+'.now()->offsetHours.')',
        ];
    }
}
