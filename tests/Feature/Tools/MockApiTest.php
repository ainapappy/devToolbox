<?php

use App\Http\Controllers\Tools\MockApiController;
use App\Http\Requests\Tools\StoreMockRequest;
use App\Models\Mock;
use Illuminate\Support\Facades\Cache;

test('a valid mock can be created', function () {
    $response = $this->post(route('tools.mock-api.store'), [
        'payload' => '{"id":42,"name":"Ada"}',
        'status_code' => 200,
    ]);

    $response->assertRedirect(route('tools.mock-api.index'));
    $response->assertSessionHas('created');

    $mock = Mock::query()->sole();
    expect($mock->slug)->toMatch('/^[a-z0-9]{8}$/')
        ->and($mock->status_code)->toBe(200);
});

test('invalid JSON is rejected with a clear error', function () {
    $this->post(route('tools.mock-api.store'), [
        'payload' => '{invalid json',
        'status_code' => 200,
    ])->assertSessionHasErrors('payload');
});

test('payload size is limited', function () {
    $this->post(route('tools.mock-api.store'), [
        'payload' => '{"a":"'.str_repeat('x', StoreMockRequest::MAX_PAYLOAD).'"}',
        'status_code' => 200,
    ])->assertSessionHasErrors('payload');
});

test('status code must be a valid HTTP code', function () {
    $this->post(route('tools.mock-api.store'), [
        'payload' => '{}',
        'status_code' => 100,
    ])->assertSessionHasErrors('status_code');
});

test('the public endpoint returns exactly the stored payload', function () {
    $mock = Mock::factory()->create();

    $this->get(route('mocks.serve', $mock->slug))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJson($mock->payload === null ? [] : json_decode($mock->payload, true));
});

test('a custom status code is honored on the public endpoint', function () {
    $mock = Mock::factory()->create(['status_code' => 404]);

    $this->get(route('mocks.serve', $mock->slug))->assertNotFound();
});

test('an unknown slug returns 404', function () {
    $this->get(route('mocks.serve', 'inconnu'))->assertNotFound();
});

test('the public endpoint is served from the cache', function () {
    $mock = Mock::factory()->create();

    // Prime le cache via un premier appel, puis supprime la ligne SQL :
    $this->get(route('mocks.serve', $mock->slug))->assertOk();
    $mock->delete();

    // L'endpoint répond encore puisque le cache est peuplé.
    $this->get(route('mocks.serve', $mock->slug))->assertOk();
});

test('the cache is written when a mock is created', function () {
    $mock = Mock::factory()->make();

    $this->post(route('tools.mock-api.store'), [
        'payload' => $mock->payload,
        'status_code' => 200,
    ])->assertRedirect();

    $created = Mock::query()->sole();

    expect(Cache::get(MockApiController::cacheKey($created->slug)))->toBeArray();
});
