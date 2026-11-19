<?php

namespace App\Console\Commands;

use App\Ecommerce\Analytics\Contracts\WebhookAnalyticsServiceInterface;
use Illuminate\Console\Command;

class CleanupWebhookLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:cleanup {--days=30 : The number of days to keep logs for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old webhook logs from the database';

    /**
     * Execute the console command.
     */
    public function handle(WebhookAnalyticsServiceInterface $analyticsService): void
    {
        $days = (int) $this->option('days');

        $count = $analyticsService->cleanupLogs($days);

        $this->info(__('admin.cleanup.webhook_logs_success', ['count' => $count, 'days' => $days]));
    }
}
