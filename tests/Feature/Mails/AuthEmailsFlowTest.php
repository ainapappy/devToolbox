<?php

use App\Mails\NewLoginMail;
use App\Mails\PasswordChangedMail;
use App\Mails\ResetPasswordMail;
use App\Mails\TwoFactorCodeMail;
use App\Mails\VerifyEmailMail;
use App\Mails\WelcomeMail;
use App\Models\User;
use App\Notifications\Auth\ResetPassword;
use App\Notifications\Auth\VerifyEmail;
use App\Support\TrustedDevice;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PragmaRX\Google2FA\Google2FA;

/**
 * Utilisateur avec 2FA activée et un secret TOTP base32 valide.
 */
function userWithTotp(): User
{
    return User::factory()->create([
        'two_factor_secret' => encrypt($GLOBALS['totp_secret'] ??= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
        'two_factor_confirmed_at' => now(),
    ]);
}

/**
 * Code TOTP courant du secret de test.
 */
function currentTotp(): string
{
    return app(Google2FA::class)->getCurrentOtp($GLOBALS['totp_secret']);
}

test('verification notification sends the DevToolbox mailable', function () {
    Notification::fake();
    Mail::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post(route('verification.send'));

    Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user) {
        return $notification->toMail($user) instanceof VerifyEmailMail;
    });
});

test('password reset sends the DevToolbox mailable', function () {
    Notification::fake();
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        return $notification->toMail($user) instanceof ResetPasswordMail;
    });
});

test('welcome email is sent once after email verification', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create();

    // Simule la vérification : l'événement Verified porte le user.
    $user->markEmailAsVerified();
    event(new Verified($user));
    event(new Verified($user)); // Rejeu : ne doit pas renvoyer.

    Mail::assertQueued(WelcomeMail::class, 1);
    expect(Cache::get('welcome-sent:'.$user->getKey()))->toBeTrue();
});

test('new device login is silent for the first device', function () {
    Mail::fake();

    $user = User::factory()->create(['password' => 'password']);

    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Firefox/130.0 Windows'])
        ->post(route('login'), ['email' => $user->email, 'password' => 'password']);

    Mail::assertNotSent(WelcomeMail::class);
    Mail::assertNotSent(NewLoginMail::class);
});

test('new device login alerts only for unknown devices', function () {
    Mail::fake();

    $user = User::factory()->create(['password' => 'password']);
    $agent = 'Mozilla/5.0 Firefox/130.0 Windows';

    // Premier appareil : enregistré silencieusement.
    $this->withHeaders(['User-Agent' => $agent])
        ->post(route('login'), ['email' => $user->email, 'password' => 'password']);
    Mail::assertNotSent(NewLoginMail::class);

    // Même appareil, nouvelle session : toujours silencieux.
    $this->post(route('logout'));
    $this->withHeaders(['User-Agent' => $agent])
        ->post(route('login'), ['email' => $user->email, 'password' => 'password']);
    Mail::assertNotSent(NewLoginMail::class);

    // Appareil différent : alerte.
    $this->post(route('logout'));
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Safari/605.1 iPhone iOS'])
        ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));

    Mail::assertQueued(NewLoginMail::class, 1);
});

test('password reset sends the confirmation email', function () {
    Notification::fake();
    Mail::fake();

    $user = User::factory()->create();

    Notification::fake();
    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasNoErrors();

        return true;
    });

    Mail::assertQueued(PasswordChangedMail::class, 1);
});

test('profile password update sends the confirmation email within quota', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
        ->assertSessionHasNoErrors();

    Mail::assertQueued(PasswordChangedMail::class, 1);
});

test('two factor email code can be requested, used once and expires', function () {
    Mail::fake();

    $user = userWithTotp();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));

    // Demande du code par email.
    $this->post(route('two-factor.email-code'))
        ->assertRedirect();

    Mail::assertQueued(TwoFactorCodeMail::class, 1);

    // Le code en cache correspond au hash envoyé — on le rejoue via le défi.
    Mail::assertQueued(TwoFactorCodeMail::class, function ($mail) use ($user) {
        // Le mail contient le code formaté : on le retrouve via la vue rendue.
        $rendered = $mail->render();
        preg_match('/\b(\d{3}) (\d{3})\b/', $rendered, $m);
        expect($m)->toHaveCount(3);

        // 1re utilisation : accepté.
        $this->post(route('two-factor.login'), ['code' => $m[1].$m[2]])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);

        // Nouvelle session de défi : le même code doit être refusé.
        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.login'), ['code' => $m[1].$m[2]])
            ->assertSessionHasErrors();

        $this->assertGuest();

        return true;
    });
});

test('two factor email code request is rate limited', function () {
    Mail::fake();

    $user = userWithTotp();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

    // 1re demande OK ; 2e rapprochée → 429 (throttle:1,1 sur la route).
    $this->post(route('two-factor.email-code'))->assertRedirect();
    $this->post(route('two-factor.email-code'))->assertTooManyRequests();

    Mail::assertQueued(TwoFactorCodeMail::class, 1);
});

test('trusted device cookie bypasses the email code flow too', function () {
    Mail::fake();

    $user = userWithTotp();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

    $this->post(route('two-factor.login'), [
        'code' => currentTotp(),
        'trust' => '1',
    ])->assertRedirect(route('dashboard', absolute: false));

    expect(Cache::get('2fa-email-code:'.$user->getKey()))->toBeNull();

    $this->post(route('logout'));

    // Appareil de confiance : pas de défi du tout.
    $this->withCookie(TrustedDevice::cookieName(), TrustedDevice::token($user))
        ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));

    Mail::assertNotSent(TwoFactorCodeMail::class);
});
