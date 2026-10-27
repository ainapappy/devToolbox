<?php

namespace App\Jobs;

use App\Models\Click;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Enregistre un clic de redirection de façon asynchrone.
 */
class TrackClick implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly int $urlId,
        public readonly ?string $userAgent,
        public readonly ?string $referer,
        public readonly ?string $countryCode,
    ) {}

    public function handle(): void
    {
        Click::create([
            'url_id' => $this->urlId,
            'browser' => self::detectBrowser($this->userAgent),
            'country_code' => self::normalizeCountry($this->countryCode),
            'created_at' => now(),
        ]);
    }

    /**
     * Détection de navigateur basique depuis le User-Agent.
     */
    private static function detectBrowser(?string $userAgent): ?string
    {
        if ($userAgent === null) {
            return null;
        }

        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Safari') => 'Safari',
            default => 'Autre',
        };
    }

    /**
     * Code pays ISO 3166-1 alpha-2 si fourni (ex. header CDN geo).
     */
    private static function normalizeCountry(?string $countryCode): ?string
    {
        $normalized = strtoupper(trim((string) $countryCode));

        return preg_match('/^[A-Z]{2}$/', $normalized) ? $normalized : null;
    }
}
