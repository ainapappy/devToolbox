<?php

use App\Http\Requests\Tools\StoreDocumentRequest;
use App\Models\Document;
use App\Services\MarkdownRenderer;

test('a document can be saved and returns a share link', function () {
    $response = $this->post(route('tools.markdown.store'), [
        'content' => "# Titre\n\nDu contenu.",
    ]);

    $response->assertRedirect(route('tools.markdown.index'));
    $response->assertSessionHas('created');

    $document = Document::query()->sole();
    expect($document->slug)->toMatch('/^[a-z0-9]{8}$/');
});

test('content is required and size limited', function () {
    $this->post(route('tools.markdown.store'), ['content' => ''])
        ->assertSessionHasErrors('content');

    $this->post(route('tools.markdown.store'), [
        'content' => str_repeat('a', StoreDocumentRequest::MAX_CONTENT + 1),
    ])->assertSessionHasErrors('content');
});

test('the shared document renders markdown server-side', function () {
    $document = Document::factory()->create([
        'content' => "# Titre\n\nUn **gras**.",
    ]);

    $this->get(route('tools.markdown.show', $document->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tools/markdown/show')
            ->where('html', "<h1>Titre</h1>\n<p>Un <strong>gras</strong>.</p>\n"));
});

test('malicious scripts are neutralised server-side', function () {
    $document = Document::factory()->create([
        'content' => 'Hello <script>alert(1)</script> [lien](javascript:alert(2))',
    ]);

    $this->get(route('tools.markdown.show', $document->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('html', fn ($html) => str_contains((string) $html, '&lt;script&gt;'))
            ->where('html', fn ($html) => ! str_contains((string) $html, 'javascript:')));
});

test('an unknown slug returns 404', function () {
    $this->get(route('tools.markdown.show', 'inconnu00'))->assertNotFound();
});

test('the renderer escapes raw HTML', function () {
    $html = MarkdownRenderer::toHtml('Texte avec <img src=x onerror=alert(1)> image');

    expect($html)->not->toContain('<img');
});
