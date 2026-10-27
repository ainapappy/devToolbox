<?php

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Mock du driver GitHub renvoyant un utilisateur Socialite donné.
 *
 * @param  array{id: int, name?: string, nickname?: string, email: string}  $attributes
 */
function fakeGithubUser(array $attributes): void
{
    $user = new SocialiteUser;
    $user->setRaw($attributes);
    $user->map($attributes);

    Socialite::shouldReceive('driver')
        ->with('github')
        ->andReturnSelf();
    Socialite::shouldReceive('user')
        ->andReturn($user);
}

beforeEach(function () {
    config()->set('services.github.client_id', 'test');
    config()->set('services.github.client_secret', 'test');
});

test('a new social user is created, verified and logged in', function () {
    fakeGithubUser([
        'id' => 12345,
        'name' => 'Aina Papy',
        'email' => 'aina@example.com',
    ]);

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $user = User::query()->where('email', 'aina@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->socialAccounts()->where('provider', 'github')->exists())->toBeTrue();

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('an existing verified local user is linked and logged in', function () {
    $local = User::factory()->create([
        'email' => 'fusion@example.com',
        'email_verified_at' => now(),
    ]);

    fakeGithubUser([
        'id' => 98765,
        'name' => 'Fusion User',
        'email' => 'fusion@example.com',
    ]);

    $this->get(route('social.callback', ['provider' => 'github']))
        ->assertRedirect(route('dashboard', absolute: false));

    expect($local->socialAccounts()->where('provider', 'github')->exists())->toBeTrue()
        ->and(User::query()->count())->toBe(1);

    $this->assertAuthenticatedAs($local);
});

test('an existing unverified local user is refused (anti-takeover)', function () {
    User::factory()->create([
        'email' => 'unverified@example.com',
        'email_verified_at' => null,
    ]);

    fakeGithubUser([
        'id' => 11111,
        'name' => 'Takeover Attempt',
        'email' => 'unverified@example.com',
    ]);

    $response = $this->get(route('social.callback', ['provider' => 'github']));

    $this->assertGuest();
    expect(SocialAccount::query()->count())->toBe(0);
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status');
});

test('a cancelled OAuth return lands on login with a neutral message', function () {
    $response = $this->get(route('social.callback', [
        'provider' => 'github',
        'error' => 'access_denied',
        'error_description' => 'The user has denied your application access.',
    ]));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status');
});

test('an already linked user is logged back in', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    SocialAccount::factory()->for($user)->create([
        'provider' => 'github',
        'provider_id' => '4242',
    ]);

    fakeGithubUser([
        'id' => 4242,
        'name' => $user->name,
        'email' => $user->email,
    ]);

    $this->get(route('social.callback', ['provider' => 'github']))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('an unknown provider is refused', function () {
    $this->get(route('social.redirect', ['provider' => 'discord']))
        ->assertNotFound();
});

test('the user can unlink a social provider', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    SocialAccount::factory()->for($user)->create([
        'provider' => 'github',
        'provider_id' => '4242',
    ]);

    $this->actingAs($user)
        ->delete(route('social.unlink', ['provider' => 'github']))
        ->assertRedirect();

    expect(SocialAccount::query()->count())->toBe(0);
});

test('redirect sends the user to the OAuth provider', function () {
    Socialite::shouldReceive('driver')
        ->with('github')
        ->andReturnSelf();
    Socialite::shouldReceive('redirect')
        ->andReturn(redirect('https://github.com/login/oauth/authorize'));

    $this->get(route('social.redirect', ['provider' => 'github']))
        ->assertRedirect('https://github.com/login/oauth/authorize');
});
