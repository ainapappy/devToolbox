<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tools\StoreUrlRequest;
use App\Jobs\TrackClick;
use App\Models\Click;
use App\Models\Url;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UrlShortenerController extends Controller
{
    /**
     * Clé de cache de la redirection d'un code court.
     */
    public static function cacheKey(string $code): string
    {
        return "urls.redirect.{$code}";
    }

    /**
     * Écran principal : formulaire + lien récemment créé.
     */
    public function index(): Response
    {
        return Inertia::render('tools/shortener/index', [
            'created' => session('created'),
        ]);
    }

    /**
     * Écran des statistiques agrégées (tuiles + graphique 7 jours).
     */
    public function stats(): Response
    {
        return Inertia::render('tools/shortener/stats', [
            'stats' => self::aggregateStats(),
        ]);
    }

    /**
     * Création d'un lien court (code aléatoire ou personnalisé).
     */
    public function store(StoreUrlRequest $request): RedirectResponse
    {
        $code = $request->customCode() ?? Str::lower(Str::random(6));

        $url = Url::create([
            'target_url' => $request->string('target_url')->toString(),
            'code' => $code,
        ]);

        Cache::put(self::cacheKey($code), $url->target_url);

        return redirect()
            ->route('tools.shortener.index')
            ->with('created', [
                'code' => $code,
                'target_url' => $url->target_url,
            ]);
    }

    /**
     * Redirection instantanée servie depuis le cache ; le clic est tracké
     * de façon asynchrone via le job TrackClick.
     */
    public function redirect(Request $request, string $code): RedirectResponse
    {
        $cached = Cache::rememberForever(self::cacheKey($code), function () use ($code) {
            $url = Url::query()->where('code', $code)->firstOrFail();

            return [
                'target_url' => $url->target_url,
                'url_id' => $url->id,
            ];
        });

        TrackClick::dispatch(
            $cached['url_id'],
            $request->userAgent(),
            $request->header('referer'),
            $request->header('CF-IPCountry'),
        );

        return redirect()->away($cached['target_url'], 302);
    }

    /**
     * Agrégations SQL pour l'écran de statistiques.
     *
     * @return array<string, mixed>
     */
    private static function aggregateStats(): array
    {
        $clicksLast30Days = Click::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $browserRow = Click::query()
            ->toBase()
            ->select('browser', DB::raw('count(*) as total'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->first();

        $countryRow = Click::query()
            ->toBase()
            ->select('country_code', DB::raw('count(*) as total'))
            ->whereNotNull('country_code')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->first();

        $totalForShares = max($clicksLast30Days, 1);

        $daily = Click::query()
            ->toBase()
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->day => (int) $row->total]);

        $dailySeries = collect(range(6, 0))
            ->map(fn (int $ago) => now()->subDays($ago)->startOfDay())
            ->map(fn (CarbonImmutable $day) => [
                'date' => $day->toDateString(),
                'count' => $daily->get($day->toDateString(), 0),
            ]);

        return [
            'clicks_30d' => $clicksLast30Days,
            'active_links' => Url::count(),
            'top_browser' => $browserRow?->browser,
            'top_browser_share' => $browserRow ? (int) round($browserRow->total / $totalForShares * 100) : null,
            'top_country' => $countryRow?->country_code,
            'top_country_share' => $countryRow ? (int) round($countryRow->total / $totalForShares * 100) : null,
            'daily' => $dailySeries->all(),
        ];
    }
}
