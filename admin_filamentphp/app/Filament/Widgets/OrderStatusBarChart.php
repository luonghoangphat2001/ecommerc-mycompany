<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Support\Enums\MaxWidth;
use App\Contracts\Services\AnalyticsServiceInterface;

class OrderStatusBarChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'orderStatusBarChart';

    protected static ?string $maxHeight = '300px';

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 9,
            'md' => 12,
            'sm' => 12
        ];
    }
    /**
     * Widget Title
     *
     * @var string|null
     */

    protected static MaxWidth|string $filterFormWidth = MaxWidth::MaxContent;

    protected static ?string $heading = 'Trạng thái đơn hàng';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $analyticsService = app(AnalyticsServiceInterface::class);
        $year = Carbon::now()->year;
        $orders = collect($analyticsService->getOrderStatusChartData($year));

        // Danh sách trạng thái đơn hàng (lấy từ database)
        $statuses = Order::select('status')->distinct()->pluck('status')->toArray();

        // Chuẩn bị dữ liệu cho biểu đồ
        $data = [];
        foreach ($statuses as $status) {
            $statusData = [];
            for ($i = 1; $i <= 12; $i++) {
                $count = $orders->where('month', $i)->where('status', $status)->sum('count');
                $statusData[] = ['x' => Carbon::create()->month($i)->format('M'), 'y' => [0, $count]];
            }
            $data[] = [
                'name' =>  trans('admin.' . strtolower(ucfirst($status->value))),
                'data' => $statusData, // Dữ liệu theo tháng
            ];
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => $data,
            'xaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
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
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => "function(val) { return val[1] }"
                ],
            ],
        ];
    }
}
