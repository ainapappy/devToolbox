<?php

namespace App\Console\Commands;

use App\Models\Paste;
use Illuminate\Console\Command;

class PrunePastesCommand extends Command
{
    protected $signature = 'pastes:prune';

    protected $description = 'Purge les pastes Secure Paste expirés';

    public function handle(): int
    {
        $deleted = Paste::where('expires_at', '<', now())->delete();

        $this->info("{$deleted} paste(s) expiré(s) supprimé(s).");

        return self::SUCCESS;
    }
}
