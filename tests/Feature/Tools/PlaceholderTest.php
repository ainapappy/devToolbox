<?php

use App\Http\Controllers\Tools\PlaceholderController;
use Illuminate\Support\Facades\Cache;

test('a valid request returns a proper SVG', function () {
    $response = $this->get('/placeholder/300x200?color=blue');

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/svg+xml');

    $svg = (string) $response->getContent();
    expect($svg)->toContain('<svg')
        ->toContain('#2a78d6')
        ->toContain('300 × 200');
});

test('the default color is indigo', function () {
    $this->get('/placeholder/150x150')
        ->assertOk()
        ->getContent()
        && true;

    expect((string) $this->get('/placeholder/150x150')->getContent())
        ->toContain('#6366f1');
});

test('invalid dimensions return 400', function () {
    $this->get('/placeholder/abc')->assertNotFound();
    $this->get('/placeholder/300')->assertNotFound();
    $this->get('/placeholder/0x200')->assertBadRequest();
    $this->get('/placeholder/5000x200')->assertBadRequest();
});

test('an unknown color returns 400', function () {
    $this->get('/placeholder/300x200?color=javascript%3Aalert(1)')->assertBadRequest();
    $this->get('/placeholder/300x200?color=%3Cscript%3E')->assertBadRequest();
});

test('custom text is escaped in the SVG', function () {
    $response = $this->get('/placeholder/300x200?text='.urlencode('<script>alert("xss")</script>'));

    $svg = (string) $response->getContent();
    expect($svg)->not->toContain('<script>')
        ->and($svg)->toContain('&lt;script&gt;');
});

test('responses are served from the cache', function () {
    $this->get('/placeholder/300x200?color=teal')->assertOk();

    $key = 'placeholder.300x200.teal.'.sha1('300 × 200');
    expect(Cache::has($key))->toBeTrue();
});

test('re-visits with etag return 304', function () {
    $first = $this->get('/placeholder/300x200');
    $etag = $first->getEtag();

    expect($etag)->not->toBeNull();

    $this->get('/placeholder/300x200', ['If-None-Match' => $etag])
        ->assertNotModified();
});

test('custom text changes the cache key', function () {
    PlaceholderController::palette();

    $this->get('/placeholder/300x200?text=Hello')->assertOk();
    $this->get('/placeholder/300x200?text=World')->assertOk();

    expect(Cache::has('placeholder.300x200.indigo.'.sha1('Hello')))->toBeTrue()
        ->and(Cache::has('placeholder.300x200.indigo.'.sha1('World')))->toBeTrue();
});
