<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TopProduct extends Widget
{
    protected static string $view = 'admin.widgets.top-product';

    protected static ?string $maxHeight = '350px';

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 6,
            'md' => 12,
            'sm' => 12,
        ];
    }


    public function getViewData(): array
    {
        $analyticsService = app(\App\Ecommerce\Analytics\Contracts\AnalyticsServiceInterface::class);
        $topProducts = collect($analyticsService->getTopProducts(6, app()->getLocale()));

        return [
            'topThree' => $topProducts->take(3),
            'otherProducts' => $topProducts->slice(3),
            'heading' => trans('admin.chart.header_top_product'),
            'currency' => app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol()
        ];
    }
}
