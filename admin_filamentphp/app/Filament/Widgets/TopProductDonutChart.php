<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TopProductDonutChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'TopProductDonutChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = null;


    public  function getHeading(): ?string
    {
        return __('admin.chart.TopProductDonutChart');
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     * https://filamentphp.com/plugins/leandrocfe-apex-charts
     *
     * @return array
     */

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 5,
            'sm' => 12
        ];
    }

    protected function getOptions(): array
    {
        $topProducts = collect(app(\App\Contracts\Services\AnalyticsServiceInterface::class)->getTopProductsDonutChartData(5, app()->getLocale()));

        // Chuyển dữ liệu thành mảng
        $labels = $topProducts->pluck('product_name')->toArray();
        $series = $topProducts->pluck('total_sold')->map(fn($value) => (int) $value)->toArray();


        return [
            'chart' => [
                'type' => 'donut',
                // 'height' => 500,
            ],
            'series' => $series, // Dữ liệu số lượng bán thực tế
            'labels' => $labels, // Tên sản phẩm theo ngôn ngữ
            'legend' => [
                'show' => false,
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                            ],
                            'value' => [
                                'show' => true,
                            ],
                            'total' => [
                                'show' => true,
                                'showAlways' => true,
                                'label' => __('admin.chart.TotalProductDonutChart'), // Hiển thị chữ "Total" ở giữa
                            ],
                        ],
                    ],
                ],
            ],
            'colors' => [
                '#f59e0b',
                '#10b981',
                '#3b82f6',
                '#ef4444',
                '#a855f7',
                '#6366F1',
                '#EC4899',
                '#22C55E',
                '#D97706',
                '#8B5CF6',
                '#F43F5E',
                '#14B8A6',
                '#EAB308',
                '#3B82F6',
                '#DB2777',
                '#2563EB',
                '#9D174D',
                '#1E40AF',
                '#047857',
                '#7C3AED'
            ],
            'tooltip' => [
                'enabled' => true,
                'followCursor' => true,
                'theme' => 'light',
                'y' => [
                    'formatter' => "function(value, { seriesIndex, w }) {
                        let label = w.globals.labels[seriesIndex];
                        return (label.length > 20 ? label.substring(0, 20) + '...' : label) + ': ' + value;
                    }",
                ],
            ],
        ];
    }
}
