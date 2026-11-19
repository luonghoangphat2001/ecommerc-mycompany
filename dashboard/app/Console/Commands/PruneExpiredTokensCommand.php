<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Token;

class PruneExpiredTokensCommand extends Command
{
    protected $signature = 'auth:prune-tokens {--retention-days=7 : Keep expired token rows for this many days}';

    protected $description = 'Remove expired Passport token records beyond the retention window';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('retention-days'));
        $deleted = 0;

        Token::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $cutoff)
            ->chunkById(500, function ($tokens) use (&$deleted): void {
                    $deleted += Token::query()
                    ->whereKey($tokens->modelKeys())
                    ->delete();
            });

        $this->info("Deleted {$deleted} expired token(s).");

        return self::SUCCESS;
    }
}
