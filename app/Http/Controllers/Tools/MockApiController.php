<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tools\StoreMockRequest;
use App\Models\Mock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MockApiController extends Controller
{
    /**
     * Clé de cache du payload public d'un mock.
     */
    public static function cacheKey(string $slug): string
    {
        return "mocks.payload.{$slug}";
    }

    /**
     * Écran principal de l'outil (éditeur + endpoint généré).
     */
    public function create(): Response
    {
        return Inertia::render('tools/mock-api/index', [
            'created' => session('created'),
        ]);
    }

    /**
     * Création d'un mock : payload JSON validé + slug aléatoire.
     */
    public function store(StoreMockRequest $request): RedirectResponse
    {
        $mock = Mock::create([
            'payload' => $request->string('payload')->toString(),
            'slug' => Str::lower(Str::random(8)),
            'status_code' => $request->statusCode(),
        ]);

        Cache::put(
            self::cacheKey($mock->slug),
            ['payload' => $mock->payload, 'status_code' => $mock->status_code],
            now()->addDay(),
        );

        return redirect()
            ->route('tools.mock-api.index')
            ->with('created', [
                'slug' => $mock->slug,
                'status_code' => $mock->status_code,
            ]);
    }

    /**
     * Endpoint public : renvoie le payload servi depuis le cache.
     */
    public function serve(string $slug): JsonResponse
    {
        $cached = Cache::get(self::cacheKey($slug));

        if (is_array($cached)) {
            return response()->json(
                json_decode($cached['payload'], true),
                $cached['status_code'],
                ['Cache-Control' => 'public, max-age=60'],
            );
        }

        $mock = Mock::query()->where('slug', $slug)->firstOrFail();

        // Répare le cache manquant à l'occasion d'un accès DB.
        Cache::put(
            self::cacheKey($slug),
            ['payload' => $mock->payload, 'status_code' => $mock->status_code],
            now()->addDay(),
        );

        return response()->json(
            json_decode($mock->payload, true),
            $mock->status_code,
            ['Cache-Control' => 'public, max-age=60'],
        );
    }
}
