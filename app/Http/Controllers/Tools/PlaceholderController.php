<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PlaceholderController extends Controller
{
    /**
     * Dimensions maximales acceptées (px).
     */
    public const MAX_SIZE = 4000;

    /**
     * Palette fermée : nom → couleur de base et couleur sombre (dégradé).
     *
     * @return array<string, array{base: string, dark: string}>
     */
    public static function palette(): array
    {
        return [
            'indigo' => ['base' => '#6366f1', 'dark' => '#4338ca'],
            'cyan' => ['base' => '#22d3ee', 'dark' => '#0e7490'],
            'violet' => ['base' => '#a855f7', 'dark' => '#7e22ce'],
            'orange' => ['base' => '#eb6834', 'dark' => '#c2410c'],
            'pink' => ['base' => '#e87ba4', 'dark' => '#be185d'],
            'teal' => ['base' => '#14b8a6', 'dark' => '#0f766e'],
            'blue' => ['base' => '#2a78d6', 'dark' => '#1e4f96'],
            'green' => ['base' => '#22c55e', 'dark' => '#15803d'],
            'red' => ['base' => '#ef4444', 'dark' => '#b91c1c'],
            'gray' => ['base' => '#898781', 'dark' => '#52514e'],
        ];
    }

    /**
     * Écran principal : constructeur interactif.
     */
    public function index(): Response
    {
        return Inertia::render('tools/placeholder/index');
    }

    /**
     * Génération du SVG : /placeholder/{size}?color=&text=
     * Réponse cachée (cache serveur + HTTP), 304 si inchangée côté client.
     */
    public function show(Request $request, string $size): SymfonyResponse
    {
        if (! preg_match('/^(\d{1,4})x(\d{1,4})$/', $size, $matches)) {
            abort(400, 'Format attendu : {largeur}x{hauteur} (ex. 300x200).');
        }

        $width = (int) $matches[1];
        $height = (int) $matches[2];

        if ($width < 1 || $height < 1 || $width > self::MAX_SIZE || $height > self::MAX_SIZE) {
            abort(400, sprintf('Dimensions acceptées : 1 à %d par côté.', self::MAX_SIZE));
        }

        $color = $request->query('color', 'indigo');
        $colors = self::palette();

        if (! is_string($color) || ! isset($colors[$color])) {
            abort(400, 'Couleur inconnue. Palette : '.implode(', ', array_keys($colors)).'.');
        }

        $text = $request->query('text');
        $label = is_string($text) && $text !== ''
            ? mb_substr($text, 0, 60)
            : sprintf('%d × %d', $width, $height);

        $key = sprintf(
            'placeholder.%dx%d.%s.%s',
            $width,
            $height,
            $color,
            sha1($label),
        );

        $svg = Cache::rememberForever($key, fn () => self::buildSvg(
            $width,
            $height,
            $colors[$color]['base'],
            $colors[$color]['dark'],
            $label,
        ));

        $response = response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=31536000, immutable');

        $response->setEtag('"'.md5($key.$svg).'"');

        // Renvoie un 304 si le client a déjà cette version.
        $response->isNotModified($request);

        return $response;
    }

    /**
     * Construit le SVG (valeurs entièrement contrôlées : aucune injection).
     */
    private static function buildSvg(
        int $width,
        int $height,
        string $base,
        string $dark,
        string $label,
    ): string {
        $fontSize = max(12, (int) round(min($width, $height) / 10));
        $gradientId = 'pg';
        $centerX = (int) round($width / 2);
        $centerY = (int) round($height / 2);
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" role="img" aria-label="Image placeholder {$width}x{$height}">
  <defs>
    <linearGradient id="{$gradientId}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$base}"/>
      <stop offset="100%" stop-color="{$dark}"/>
    </linearGradient>
  </defs>
  <rect width="{$width}" height="{$height}" rx="12" fill="url(#{$gradientId})"/>
  <text x="{$centerX}" y="{$centerY}" text-anchor="middle" dominant-baseline="middle" font-family="system-ui, sans-serif" font-size="{$fontSize}" font-weight="700" fill="#ffffff">{$label}</text>
</svg>
SVG;
    }
}
