<?php

namespace App\Ecommerce\Analytics\Repositories;

use App\Ecommerce\Analytics\Contracts\WebhookAnalyticsRepositoryInterface;
use App\Models\WebhookLog;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\DB;

class EloquentWebhookAnalyticsRepository implements WebhookAnalyticsRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getDeliveryTrend(int $days = 7): array
    {
        $data = Trend::model(WebhookLog::class)
            ->between(
                start: now()->subDays($days - 1),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'labels' => $data->map(fn (TrendValue $value) => $value->date)->toArray(),
            'values' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getStatsSummary(): array
    {
        $last24h = now()->subDay();

        $total = WebhookLog::where('created_at', '>=', $last24h)->count();
        $successCount = WebhookLog::where('status', 'delivered')
            ->where('created_at', '>=', $last24h)
            ->count();
        $failedCount = WebhookLog::where('status', 'failed')
            ->where('created_at', '>=', $last24h)
            ->count();

        return [
            'total_last_24h' => $total,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'success_rate' => $total > 0 ? (int) (($successCount / $total) * 100) : 100,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTotalWebhooksCount(): int
    {
        return \App\Models\Webhook::count();
    }
}
