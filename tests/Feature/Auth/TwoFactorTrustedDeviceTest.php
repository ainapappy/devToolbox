<?php

use App\Models\User;
use App\Support\TrustedDevice;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpFoundation\Cookie;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

/**
 * Utilisateur avec 2FA activée et un secret TOTP base32 valide.
 */
function userWithValidTwoFactor(): User
{
    return User::factory()->create([
        'two_factor_secret' => encrypt($GLOBALS['totp_secret'] ??= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'),
        'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
        'two_factor_confirmed_at' => now(),
    ]);
}

/**
 * Code TOTP courant pour le secret de test.
 */
function currentOtpForTest(): string
{
    return app(Google2FA::class)
        ->getCurrentOtp($GLOBALS['totp_secret']);
}

test('two factor challenge can be passed with a valid TOTP code', function () {
    $user = userWithValidTwoFactor();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.login'), ['code' => currentOtpForTest()])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('trusting the device skips the challenge on the next login', function () {
    $user = userWithValidTwoFactor();

    // Premier login : défi 2FA + case « appareil de confiance ».
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $response = $this->post(route('two-factor.login'), [
        'code' => currentOtpForTest(),
        'trust' => '1',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $cookie = collect($response->headers->getCookies())
        ->first(fn (Cookie $c) => $c->getName() === TrustedDevice::cookieName());
    expect($cookie)->not->toBeNull();

    // Nouvelle session (autre login, même navigateur) : pas de défi.
    // Le framework de test chiffre lui-même le cookie (CookieValuePrefix),
    // on lui passe donc le jeton en clair.
    $this->post(route('logout'));
    $this->assertGuest();

    $this->withCookie(TrustedDevice::cookieName(), TrustedDevice::token($user))
        ->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('a recovery code is consumed only once', function () {
    $user = userWithValidTwoFactor();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.login'), ['recovery_code' => 'recovery-code-1'])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'));

    // Le code a été remplacé : il ne fonctionne plus.
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->post(route('two-factor.login'), ['recovery_code' => 'recovery-code-1'])
        ->assertSessionHasErrors();

    $this->assertGuest();
});
