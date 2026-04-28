<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProductLineChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'productLineChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = null;

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 6,
            'md' => 12,
            'sm' => 12
        ];
    }

    public function __construct()
    {
        static::$heading = trans('admin.chart.productLineChart');
    }

    // Hiển thị filter
    // protected static bool $isFilterable = true;



    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     * https://filamentphp.com/plugins/leandrocfe-apex-charts
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $products = collect(app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class)->getTopProductsChartData(10, app()->getLocale()));

        // Chuẩn bị dữ liệu cho biểu đồ
        $series = [
            [
                'name' => __('admin.chart.revenue.label'),
                'data' => $products->pluck('revenue')->toArray(),
            ]
        ];

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 400,
                'toolbar' => ['show' => true],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                ]
            ],
            'series' => $series,
            'xaxis' => [
                'categories' => $products->pluck('product_name')->toArray(),
            ],
            'colors' => ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#a855f7', '#6366F1', '#EC4899', '#22C55E', '#D97706', '#8B5CF6', '#F43F5E', '#14B8A6', '#EAB308', '#3B82F6', '#DB2777', '#2563EB', '#9D174D', '#1E40AF', '#047857', '#7C3AED'],
        ];
    }
}
