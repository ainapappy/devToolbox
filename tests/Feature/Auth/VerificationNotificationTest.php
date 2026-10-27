<?php

use App\Models\User;
use App\Notifications\Auth\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::emailVerification());
});

test('sends verification notification', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('does not send verification notification if email is verified', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard', absolute: false));

    Notification::assertNothingSent();
});

test('resend is throttled server-side', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    // 1re demande : la notification part.
    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('home'));

    // 2e demande rapprochée : le limiter dédié (1/min/utilisateur) renvoie 429.
    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertTooManyRequests();

    Notification::assertSentToTimes($user, VerifyEmail::class, 1);
});
