<?php

test('dashboard is publicly accessible and lists the tools', function () {
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('dashboard')
            ->has('tools', 5)
            ->has('tools.0', fn ($tool) => $tool
                ->where('slug', 'paste')
                ->where('name', 'Secure Paste')
                ->has('description')
                ->has('tags')
                ->has('color')
                ->has('icon')),
    );
});

test('each tool page is publicly accessible', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    '/tools/paste',
    '/tools/mock-api',
    '/tools/shortener',
    '/tools/markdown',
    '/tools/placeholder',
]);
