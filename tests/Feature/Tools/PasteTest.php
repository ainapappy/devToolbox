<?php

use App\Models\Paste;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

test('a paste can be created and returns a non-sequential link', function () {
    $response = $this->post(route('tools.paste.store'), [
        'content' => 'mon secret',
        'expiration' => 'burn',
    ]);

    $response->assertRedirect(route('tools.paste.index'));
    $response->assertSessionHas('created');

    $paste = Paste::query()->sole();
    expect($paste->content)->toBe('mon secret')
        ->and($paste->burn_after_read)->toBeTrue()
        ->and($paste->hash)->toMatch('/^[a-zA-Z0-9]{32}$/');
});

test('content is required and expiration is a closed list', function () {
    $this->post(route('tools.paste.store'), [
        'content' => '',
    ])->assertSessionHasErrors('content');

    $this->post(route('tools.paste.store'), [
        'content' => 'secret',
        'expiration' => '1an',
    ])->assertSessionHasErrors('expiration');
});

test('expiration options set the right lifetime', function (string $option) {
    $this->post(route('tools.paste.store'), [
        'content' => 'secret',
        'expiration' => $option,
    ])->assertRedirect();

    $paste = Paste::query()->sole();

    expect($paste->burn_after_read)->toBeFalse()
        ->and($paste->expires_at)->not->toBeNull()
        ->and($paste->expires_at->isFuture())->toBeTrue();
})->with(['1h', '24h']);

test('a burn paste is readable exactly once', function () {
    $paste = Paste::factory()->create();

    $this->get(route('pastes.show', $paste->hash))->assertOk();

    $this->post(route('pastes.reveal', $paste->hash))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('tools/paste/reveal')->where('content', $paste->content));

    expect(Paste::query()->whereKey($paste->getKey())->exists())->toBeFalse();

    $this->post(route('pastes.reveal', $paste->hash))->assertNotFound();
});

test('an expired paste returns 404', function () {
    $paste = Paste::factory()->expired()->create();

    $this->post(route('pastes.reveal', $paste->hash))->assertNotFound();
});

test('an unknown hash returns 404', function () {
    $this->get(route('pastes.show', str_repeat('x', 32)))->assertNotFound();
    $this->post(route('pastes.reveal', str_repeat('x', 32)))->assertNotFound();
});

test('expired pastes are pruned by the scheduler command', function () {
    $expired = Paste::factory()->expired()->create();
    $valid = Paste::factory()->expiring()->create();

    Artisan::call('pastes:prune');

    expect(Paste::query()->whereKey($expired->getKey())->exists())->toBeFalse()
        ->and(Paste::query()->whereKey($valid->getKey())->exists())->toBeTrue();
});

test('the prune command is scheduled hourly', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains((string) $event->command, 'pastes:prune'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 * * * *');
});
