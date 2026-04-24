<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class WebhookDeliveryChart extends ChartWidget
{
    protected static ?string $pollingInterval = '30s';

    public function getHeading(): string
    {
        return __('admin.webhooks.stats.delivery_trend');
    }

    protected function getData(): array
    {
        $data = app(\App\Contracts\Services\WebhookAnalyticsServiceInterface::class)->getDeliveryChartData(7);

        return [
            'datasets' => [
                [
                    'label' => __('admin.webhooks.stats.deliveries'),
                    'data' => $data['values'],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#fef3c7',
                    'fill' => true,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
