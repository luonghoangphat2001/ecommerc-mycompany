<?php

namespace App\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyCustomersChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'monthlyCustomersChart';

    protected static ?string $maxHeight = '300px';

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 3,
            'md' => 12,
            'sm' => 6
        ];
    }


    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Khách hàng mới so với cùng kì';


    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $stats = app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class)->getMonthlyCustomerStats();

        return [
            'chart' => [
                'type' => 'radialBar',
                'height' => 300,
            ],
            'series' => [$stats['current_count']],
            'labels' => ['Khách hàng mới'],

            'plotOptions' => [
                'radialBar' => [
                    'hollow' => [
                        'size' => '70%',
                    ],
                    'dataLabels' => [
                        'show' => true,
                        'name' => [
                            'show' => true,
                            'fontFamily' => 'inherit'
                        ],
                        'value' => [
                            'show' => true,
                            'fontFamily' => 'inherit',
                            'fontWeight' => 600,
                            'fontSize' => '20px'
                        ],
                    ],

                ],
            ],
            'stroke' => [
                'lineCap' => 'round',
            ],

            'colors' => ['#f59e0b'],
        ];
    }

    protected function getFooter(): ?string
    {
        $stats = app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class)->getMonthlyCustomerStats();
        $currentMonth = $stats['current_count'];
        $previousMonth = $stats['previous_count'];

        return "
            <div class='flex justify-between mt-4 text-sm text-gray-600'>
                <div class='text-center my-8'>
                    <strong>Tháng này</strong>
                    <div class='text-lg font-semibold'>$currentMonth</div>
                </div>
                <div class='text-center my-8'>
                    <strong>Tháng trước</strong>
                    <div class='text-lg font-semibold'>$previousMonth</div>
                </div>
            </div>
        ";
    }
}
