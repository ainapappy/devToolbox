<?php

use App\Mails\NewLoginMail;
use App\Mails\PasswordChangedMail;
use App\Mails\ResetPasswordMail;
use App\Mails\TwoFactorCodeMail;
use App\Mails\VerifyEmailMail;
use App\Mails\WelcomeMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Utilisateur factice pour les tests de Mailables (jamais persisté).
 */
function fakeMailUser(): User
{
    return User::factory()->make(['name' => 'Alex', 'email' => 'alex@example.com']);
}

test('verify email mail has subject, recipient and signed url', function () {
    $user = fakeMailUser();
    $url = 'https://services.test/email/verify/1/token?expires=123';

    $mail = new VerifyEmailMail($user, $url);

    $mail->assertHasSubject('Confirmez votre adresse email — DevToolbox');
    $mail->assertHasTo($user->email);
    $mail->assertSeeInHtml($url);
    $mail->assertSeeInHtml('60 minutes');
    $mail->assertSeeInHtml($user->email);
});

test('welcome mail lists the tools and 2FA tip', function () {
    $user = fakeMailUser();

    $mail = new WelcomeMail($user, config('app.url'));

    $mail->assertHasSubject('Bienvenue à bord — votre compte DevToolbox est actif');
    $mail->assertSeeInHtml('Secure Paste');
    $mail->assertSeeInHtml('Astuce');
    $mail->assertSeeInHtml('Découvrir mes outils');
});

test('reset password mail contains the url and expiry', function () {
    $user = fakeMailUser();
    $url = 'https://services.test/reset-password/token-abc?email=alex%40example.com';

    $mail = new ResetPasswordMail($user, $url);

    $mail->assertHasSubject('Réinitialisation de votre mot de passe — DevToolbox');
    $mail->assertSeeInHtml($url);
    $mail->assertSeeInHtml('60 minutes');
    $mail->assertSeeInHtml('usage unique');
});

test('two factor code mail contains the formatted code', function () {
    $user = fakeMailUser();

    $mail = new TwoFactorCodeMail($user, '482913');

    $mail->assertHasSubject('Votre code de vérification — DevToolbox');
    $mail->assertSeeInHtml('482 913');
    $mail->assertSeeInHtml('10 minutes');
});

test('new login mail lists device, location, ip and date', function () {
    $user = fakeMailUser();

    $mail = new NewLoginMail($user, [
        'device' => 'Chrome sur Windows 11',
        'location' => 'Paris, France',
        'ip' => '92.184.x.x',
        'date' => '27 octobre 2026 à 14:32 (UTC+1)',
    ], config('app.url'));

    $mail->assertHasSubject('Nouvelle connexion à votre compte — DevToolbox');
    $mail->assertSeeInHtml('Chrome sur Windows 11');
    $mail->assertSeeInHtml('Paris, France');
    $mail->assertSeeInHtml('92.184.x.x');
});

test('password changed mail confirms the update', function () {
    $user = fakeMailUser();

    $mail = new PasswordChangedMail($user, '27 octobre 2026 à 14:32');

    $mail->assertHasSubject('Votre mot de passe a été mis à jour — DevToolbox');
    $mail->assertSeeInHtml('mot de passe');
    $mail->assertSeeInHtml('sessions');
});

test('mailables are queued', function () {
    expect(new VerifyEmailMail(fakeMailUser(), 'https://x'))->toBeInstanceOf(ShouldQueue::class)
        ->and(new WelcomeMail(fakeMailUser(), 'https://x'))->toBeInstanceOf(ShouldQueue::class)
        ->and(new ResetPasswordMail(fakeMailUser(), 'https://x'))->toBeInstanceOf(ShouldQueue::class)
        ->and(new TwoFactorCodeMail(fakeMailUser(), '123456'))->toBeInstanceOf(ShouldQueue::class)
        ->and(new NewLoginMail(fakeMailUser(), ['device' => 'd', 'location' => 'l', 'ip' => 'i', 'date' => 'd'], 'https://x'))->toBeInstanceOf(ShouldQueue::class)
        ->and(new PasswordChangedMail(fakeMailUser()))->toBeInstanceOf(ShouldQueue::class);
});
