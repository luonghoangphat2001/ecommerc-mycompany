<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Support\RawJs;
use App\Settings\DBSettings;
use App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface;

class RevenueLineChart extends ChartWidget
{
    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 12,
            'md' => 12,
            'sm' => 12
        ];
    }

    protected static ?string $maxHeight = '400px';

    public  function getHeading(): ?string
    {
        return __('admin.chart.order');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'this_year' => trans('admin.chart.this_year'),
            'last_year' => trans('admin.chart.last_year'),
            'all_time' => trans('admin.chart.all_time'),
        ];
    }

    protected function getOptions(): RawJs
    {
        $currencyService = app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class);

        return RawJs::make(<<<JS
            {
                plugins: {
                title: {
                    display: true,
                    text: 'Đơn vị: {$currencyService->getCurrencySymbol()}'
                }
            },
            }
        JS);
    }

    protected function getData(): array
    {
        $data = app(AnalyticsServiceInterface::class)->getRevenueChartData($this->filter ?? 'this_year');

        return [
            'datasets' => [
                [
                    'label' => trans('admin.chart.order'),
                    'data' => $data,
                    'type' => 'bar',
                    'backgroundColor' => '#f59e0b',
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
