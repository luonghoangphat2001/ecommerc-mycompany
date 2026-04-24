<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Contracts\Services\AnalyticsServiceInterface;

class CustomersLineChart extends ChartWidget
{

    public  function getHeading(): ?string
    {
        return __('admin.chart.customers_by_year');
    }

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 12,
            'md' => 12,
            'sm' => 12,
        ];
    }


    protected static ?string $maxHeight = '300px';

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

    protected function getData(): array
    {
        $data = app(AnalyticsServiceInterface::class)->getCustomerChartData($this->filter ?? 'this_year');
        return [
            'datasets' => [
                [
                    'label' => trans('admin.chart.customer'),
                    'data' => array_values($data),
                    'fill' => 'start',
                    'tension' => 0.5, // Làm đường cong mượt hơn
                    'pointRadius' => 0, // Bỏ dấu chấm
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
