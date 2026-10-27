<?php

use App\Models\User;
use App\Notifications\Auth\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create();

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertSessionHasErrors('email');
});

test('password reset invalidates other sessions', function () {
    $user = User::factory()->create();

    // Simule une session active en base pour cet utilisateur.
    DB::table('sessions')->insert([
        'id' => $sessionId = 'session-test-'.uniqid(),
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'payload' => base64_encode(''),
        'last_activity' => time(),
    ]);

    Notification::fake();
    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();

        return true;
    });

    // La session préexistante a été supprimée par le reset.
    expect(DB::table('sessions')->where('id', $sessionId)->exists())->toBeFalse();
});

test('reset link requests are rate limited per IP', function () {
    // Adresses distinctes : on ne veut tester que le limiter par IP,
    // pas le throttle interne du broker (60 s par compte).
    foreach (range(1, 3) as $i) {
        $this->post(route('password.email'), ['email' => "user{$i}@example.com"])
            ->assertSessionHasNoErrors();
    }

    // 4e requête dans la même minute : le limiter dédié (3/min/IP) bloque.
    $this->post(route('password.email'), ['email' => 'user4@example.com'])
        ->assertTooManyRequests();
});
