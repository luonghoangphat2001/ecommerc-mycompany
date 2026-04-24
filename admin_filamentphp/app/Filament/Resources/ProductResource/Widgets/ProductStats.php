<?php



namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Resources\ProductResource\Pages\ListProducts;

use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Traits\HasCurrencyFormat;

class ProductStats extends BaseWidget
{
    use HasCurrencyFormat;
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListProducts::class;
    }


    protected function getStats(): array
    {
        return [
            Stat::make(trans('admin.product.statistical.total'), $this->getPageTableQuery()->count()),
            Stat::make(trans('admin.product.statistical.inventory'), $this->getPageTableQuery()->sum('qty')),
            Stat::make(
                trans('admin.product.statistical.average'),
                self::formatMoney_not_symbol($this->getPageTableQuery()->avg('price'))
            ),
        ];
    }
}
