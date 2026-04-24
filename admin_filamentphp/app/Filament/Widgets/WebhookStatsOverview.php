<?php

namespace App\Filament\Widgets;

use App\Models\Webhook;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebhookStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $service = app(\App\Contracts\Services\WebhookAnalyticsServiceInterface::class);
        $stats = $service->getStatsOverview();

        $currencyService = app(\App\Contracts\Services\CurrencyServiceInterface::class);

        return [
            Stat::make(__('admin.webhooks.stats.total_webhooks'), $currencyService->formatNumber($service->getTotalWebhooksCount()))
                ->description(__('admin.webhooks.stats.total_webhooks_desc'))
                ->descriptionIcon('heroicon-m-rss'),
            Stat::make(__('admin.webhooks.stats.success_rate'), $stats['success_rate'] . '%')
                ->description(__('admin.webhooks.stats.success_rate_desc'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('admin.webhooks.stats.failed_deliveries'), $currencyService->formatNumber($stats['failed_count']))
                ->description(__('admin.webhooks.stats.failed_deliveries_desc'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
