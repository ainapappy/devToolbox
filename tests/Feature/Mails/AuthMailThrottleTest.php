<?php

use App\Models\User;
use App\Support\AuthMailThrottle;

test('auth mail quota allows 5 per hour then blocks', function () {
    $user = User::factory()->create();

    $sent = 0;

    foreach (range(1, 6) as $i) {
        if (AuthMailThrottle::attempt($user, function () use (&$sent): void {
            $sent++;
        })) {
            // Continu jusqu'au refus.
        }
    }

    expect($sent)->toBe(5);
});

test('quota is per user, not global', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    foreach (range(1, 5) as $i) {
        AuthMailThrottle::attempt($a, fn (): null => null);
    }

    // L'utilisateur B n'est pas affecté par le quota de A.
    expect(AuthMailThrottle::attempt($b, fn (): null => null))->toBeTrue();
});
