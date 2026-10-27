<?php

use App\Models\User;

test('an unverified authenticated user is blocked from the tools', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('tools.paste.index'));

    $response->assertRedirect(route('verification.notice'));
});

test('a verified user can access the tools', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('tools.paste.index'));

    $response->assertOk();
});

test('guests can still access the tools', function () {
    $this->get(route('tools.paste.index'))->assertOk();
});
