<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tools\StoreDocumentRequest;
use App\Models\Document;
use App\Services\MarkdownRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MarkdownController extends Controller
{
    /**
     * Écran principal : éditeur scindé avec aperçu temps réel.
     */
    public function index(): Response
    {
        return Inertia::render('tools/markdown/index', [
            'created' => session('created'),
        ]);
    }

    /**
     * Sauvegarde d'un document → lien de partage.
     */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $document = Document::create([
            'content' => $request->string('content')->toString(),
            'slug' => Str::lower(Str::random(8)),
        ]);

        return redirect()
            ->route('tools.markdown.index')
            ->with('created', ['slug' => $document->slug]);
    }

    /**
     * Document partagé, rendu côté serveur (HTML échappé).
     */
    public function show(string $slug): Response
    {
        $document = Document::query()->where('slug', $slug)->firstOrFail();

        return Inertia::render('tools/markdown/show', [
            'markdown' => $document->content,
            'html' => MarkdownRenderer::toHtml($document->content),
        ]);
    }
}
