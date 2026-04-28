<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Settings\DBSettings;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 12,
            'md' => 12,
        ];
    }

    protected function getStats(): array
    {
        $analyticsService = app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class);
        $data = $analyticsService->getStatsSummary($this->filters ?? []);

        $currencyService = app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class);

        return [
            Stat::make(
                trans('admin.chart.revenue.label'),
                $currencyService->format($data['totalRevenue'])
            )->extraAttributes([
                'style' => 'background: linear-gradient(to right, #dc992d, #fda085);',
                'class' => 'custom-cart-icon custom-icon'
            ]),

            Stat::make(trans('admin.chart.new_customers'), $currencyService->formatNumber($data['newCustomers']))
                ->extraAttributes([
                    'style' => 'background: linear-gradient(to right, #dc992d, #f0712a);',
                    'class' => 'custom-cart-icon custom-icon'
                ]),

            Stat::make(trans('admin.chart.new_order'), $currencyService->formatNumber($data['totalOrders']))
                ->extraAttributes([
                    'style' => 'background: linear-gradient(to right, #dc992d, #fccb4a);',
                    'class' => 'custom-cart-icon custom-icon'
                ]),

            Stat::make(trans('admin.chart.pending_orders'), $currencyService->formatNumber($data['pendingOrders']))
                ->extraAttributes([
                    'style' => 'background: linear-gradient(to right, #dc992d, #ff9800);',
                    'class' => 'custom-cart-icon custom-icon'
                ]),
        ];
    }
}
