<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Hotfix MariaDB : index trop longs (max 1000 bytes) avec utf8mb4
        // sur l'hébergement — 191 × 4 = 764 bytes < 1000.
        Schema::defaultStringLength(191);

        // Révèle les N+1 dès le développement (exception en test/dev).
        Model::preventLazyLoading(! app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // Règles alignées sur la jauge de force frontend (Phase 10) :
        // min. 8 caractères, majuscule + minuscule, chiffre, symbole.
        Password::defaults(fn (): Password => app()->isProduction()
            ? Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols(),
        );
    }
}
