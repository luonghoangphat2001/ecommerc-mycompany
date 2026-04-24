<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Support\Enums\MaxWidth;

class OrderStatusPieChart extends ApexChartWidget
{
    protected static ?string $chartId = 'orderStatusPieChart';

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 3,
            'md' => 12,
            'sm' => 6
        ];
    }

    protected static MaxWidth|string $filterFormWidth = MaxWidth::MinContent;


    protected static ?string $maxHeight = '400px';

    protected static ?string $heading = 'Đơn hàng trong tháng';

    protected function getOptions(): array
    {
        $orders = app(\App\Contracts\Services\AnalyticsServiceInterface::class)->getOrderStatusDistribution();

        // Tổng số đơn hàng
        $totalOrders = array_sum($orders);

        // Tính phần trăm từng trạng thái
        $series = [];
        $labels = [];

        foreach ($orders as $status => $count) {
            $percentage = $totalOrders > 0 ? ($count / $totalOrders) * 100 : 0;
            $series[] = round($percentage, 2);
            $labels[] = trans('admin.' . strtolower($status));
        }

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $series, // Dữ liệu phần trăm
            'labels' => $labels, // Nhãn trạng thái đơn hàng
            'legend' => [
                'show' => true,
                'position' => 'bottom',
                'labels' => [
                    'fontFamily' => 'inherit',
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
