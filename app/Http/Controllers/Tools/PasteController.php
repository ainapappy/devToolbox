<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tools\StorePasteRequest;
use App\Models\Paste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PasteController extends Controller
{
    /**
     * Écran principal de l'outil Secure Paste (formulaire + lien généré).
     */
    public function create(): Response
    {
        return Inertia::render('tools/paste/index', [
            'created' => session('created'),
        ]);
    }

    /**
     * Création d'un paste : contenu + option d'expiration → lien sécurisé.
     */
    public function store(StorePasteRequest $request): RedirectResponse
    {
        $paste = Paste::create([
            'content' => $request->string('content')->toString(),
            'expires_at' => $request->expiresAt(),
            'burn_after_read' => $request->burnAfterRead(),
            'hash' => Str::random(32),
        ]);

        return redirect()
            ->route('tools.paste.index')
            ->with('created', [
                'hash' => $paste->hash,
                'burn_after_read' => $paste->burn_after_read,
            ]);
    }

    /**
     * Page d'avertissement avant révélation du secret (sans contenu).
     */
    public function show(string $hash): Response
    {
        $paste = Paste::query()->where('hash', $hash)->firstOrFail();

        if ($paste->isExpired()) {
            return Inertia::render('tools/paste/expired');
        }

        return Inertia::render('tools/paste/show', [
            'hash' => $paste->hash,
            'burn_after_read' => $paste->burn_after_read,
        ]);
    }

    /**
     * Révélation du secret ; un paste "burn" est détruit à cet instant.
     */
    public function reveal(Request $request, string $hash): SymfonyResponse
    {
        $paste = Paste::query()->where('hash', $hash)->firstOrFail();

        if ($paste->isExpired()) {
            // Lien périmé : page dédiée plutôt qu'une erreur. La ligne reste
            // en base jusqu'au prune planifié (pastes:prune) pour que les
            // visites suivantes voient la même information.
            return Inertia::render('tools/paste/expired')->toResponse($request);
        }

        $content = $paste->content;

        if ($paste->burn_after_read) {
            $paste->delete();
        }

        $response = Inertia::render('tools/paste/reveal', [
            'content' => $content,
        ])->toResponse($request);

        // Jamais de mise en cache du contenu secret.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');

        return $response;
    }
}
