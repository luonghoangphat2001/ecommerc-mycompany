<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use App\Traits\HasCurrencyFormat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrderStats extends BaseWidget
{
    use InteractsWithPageTable, HasCurrencyFormat;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }

    protected function getStats(): array
    {
        $orderData = Trend::query($this->getPageTableQuery()->reorder())
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            Stat::make(trans('admin.order.label'), $this->getPageTableQuery()->count())
                ->chart(
                    $orderData
                        ->map(fn(TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make(trans('admin.order.order_open'), $this->getPageTableQuery()->open()->count()),
            Stat::make(trans('admin.order.total_price'), $this->formatMoney_not_symbol($this->getPageTableQuery()->avg('total'))),
        ];
    }
}
