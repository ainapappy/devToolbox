<?php

use Illuminate\Support\Facades\Schedule;

// Purge horaire des pastes Secure Paste expirés.
Schedule::command('pastes:prune')->hourly();

// Traitement de la queue (emails register/auth, clicks…) — hébergement
// mutualisé sans supervisor : le worker se vide puis s'arrête.
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();
