<?php

use App\Jobs\TrackClick;
use App\Models\Click;
use App\Models\Url;
use Illuminate\Support\Facades\Queue;

test('a valid URL can be shortened', function () {
    $response = $this->post(route('tools.shortener.store'), [
        'target_url' => 'https://exemple.com/article-tres-long',
    ]);

    $response->assertRedirect(route('tools.shortener.index'));
    $response->assertSessionHas('created');

    $url = Url::query()->sole();
    expect($url->target_url)->toBe('https://exemple.com/article-tres-long')
        ->and($url->code)->toMatch('/^[a-z0-9]{6}$/');
});

test('the code can be customised when free', function () {
    $this->post(route('tools.shortener.store'), [
        'target_url' => 'https://exemple.com/a',
        'code' => 'monlien',
    ]);

    expect(Url::query()->sole()->code)->toBe('monlien');
});

test('invalid URLs are rejected', function () {
    $this->post(route('tools.shortener.store'), [
        'target_url' => 'pas-une-url',
    ])->assertSessionHasErrors('target_url');

    $this->post(route('tools.shortener.store'), [
        'target_url' => 'https://exemple.com/'.str_repeat('a', 2100),
    ])->assertSessionHasErrors('target_url');
});

test('an unknown code returns 404', function () {
    $this->get(route('urls.redirect', 'inconnu'))->assertNotFound();
});

test('the redirect is served from the cache', function () {
    Queue::fake();

    $url = Url::factory()->create();

    $this->get(route('urls.redirect', $url->code))
        ->assertRedirect($url->target_url, 302);

    $url->delete();

    // La redirection répond toujours : elle vient du cache.
    $this->get(route('urls.redirect', $url->code))
        ->assertRedirect($url->target_url, 302);
});

test('clicks are tracked asynchronously', function () {
    Queue::fake();

    $url = Url::factory()->create();

    $this->get(route('urls.redirect', $url->code))
        ->assertRedirect();

    Queue::assertPushed(TrackClick::class, fn (TrackClick $job) => $job->urlId === $url->id);
});

test('the TrackClick job records the click with browser detection', function () {
    $url = Url::factory()->create();

    (new TrackClick(
        $url->id,
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
        'https://google.com',
        'fr',
    ))->handle();

    $click = Click::query()->sole();
    expect($click->url_id)->toBe($url->id)
        ->and($click->browser)->toBe('Chrome')
        ->and($click->country_code)->toBe('FR');
});

test('stats are aggregated in SQL', function () {
    $url = Url::factory()->create();
    Click::factory()->count(3)->create(['url_id' => $url->id, 'browser' => 'Chrome', 'country_code' => 'FR']);
    Click::factory()->count(1)->create(['url_id' => $url->id, 'browser' => 'Firefox', 'country_code' => 'US']);

    $this->get(route('tools.shortener.stats'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tools/shortener/stats')
            ->where('stats.clicks_30d', 4)
            ->where('stats.active_links', 1)
            ->where('stats.top_browser', 'Chrome')
            ->where('stats.top_browser_share', 75)
            ->where('stats.top_country', 'FR')
            ->has('stats.daily', 7));
});

test('daily series always covers 7 days', function () {
    Click::factory()->count(2)->create();

    $this->get(route('tools.shortener.stats'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('stats.daily', 7));
});
