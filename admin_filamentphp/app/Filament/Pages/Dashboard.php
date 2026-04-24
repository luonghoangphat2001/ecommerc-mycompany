<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends BaseDashboard
{
    // use BaseDashboard\Concerns\HasFiltersForm;

    protected static ?int $navigationSort = -1;

    // public function getHeaderActions(): array
    // {
    //     // return [
    //     //     Action::make('filter')
    //     //         ->label(false) // Dùng __() thay cho trans() để ngắn gọn
    //     //         ->icon('heroicon-o-funnel')

    //     //         ->modalHeading(__('admin.data_set'))
    //     //         ->form($this->getFiltersForm()) // Sử dụng function trả về mảng schema
    //     //         ->action(function (array $data) {}),
    //     // ];
    // }

    protected function getFiltersForm(): array
    {
        return [
            Section::make(trans('admin.fillter_data'))
                ->schema([
                    Select::make('businessCustomersOnly')
                        ->label(trans('admin.chart.businessCustomersOnly'))
                        ->boolean(),
                    DatePicker::make('startDate')
                        ->label(trans('admin.chart.startDate'))
                        ->maxDate(now()),
                    DatePicker::make('endDate')
                        ->label(trans('admin.chart.endDate'))
                        ->minDate(fn($get) => $get('startDate') ?: now())
                        ->maxDate(now()),
                ])
            // ->columns(3),
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverviewWidget::class,   // nơi hiển thị phần tử khung user profile và phiên bản Filament
            \App\Filament\Widgets\RevenueLineChart::class,
            \App\Filament\Widgets\OrderStatusBarChart::class,
            \App\Filament\Widgets\OrderStatusPieChart::class,
            \App\Filament\Widgets\ProductLineChart::class,
            \App\Filament\Widgets\TopCustomers::class,
            \App\Filament\Widgets\NewCustomersTable::class,
            \App\Filament\Widgets\MonthlyCustomersChart::class,
            \App\Filament\Widgets\CustomersLineChart::class,
            \App\Filament\Widgets\LatestOrdersTable::class,
            // \Croustibat\FilamentJobsMonitor\Resources\QueueMonitorResource\Widgets\QueueStatsOverview::class,
        ];
    }
}
