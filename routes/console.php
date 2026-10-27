<?php

use Illuminate\Support\Facades\Schedule;

// Purge horaire des pastes Secure Paste expirés.
Schedule::command('pastes:prune')->hourly();
