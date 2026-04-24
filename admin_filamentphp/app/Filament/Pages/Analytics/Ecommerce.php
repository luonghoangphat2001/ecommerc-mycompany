<?php

namespace App\Filament\Pages\Analytics;

use Filament\Pages\Page;


class Ecommerce extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'admin.pages.analytics.ecommerce';

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return trans('admin.analytics.ecommerce.label');
    }

    public function getTitle(): string
    {
        return trans('admin.analytics.ecommerce.label');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverviewWidget::class,
            \App\Filament\Widgets\RevenueLineChart::class,
            \App\Filament\Widgets\OrderStatusBarChart::class,
            \App\Filament\Widgets\OrderStatusPieChart::class,
            // \App\Filament\Widgets\TopCustomers::class,
            \App\Filament\Widgets\NewCustomersTable::class,
            \App\Filament\Widgets\MonthlyCustomersChart::class,
            \App\Filament\Widgets\CustomersLineChart::class,

            \App\Filament\Widgets\ProductLineChart::class,
            \App\Filament\Widgets\TopProductDonutChart::class,
            \App\Filament\Widgets\LatestOrdersTable::class,

        ];
    }
}
