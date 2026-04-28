<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TopCustomers extends Widget
{
    protected static string $view = 'admin.widgets.top-customers';

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
        $customers = collect($analyticsService->getTopCustomers(6))->map(fn($item) => (object)$item);

        return [
            'topThree' => $customers->take(3),
            'otherCustomers' => $customers->slice(3),
            'heading' => trans('admin.chart.header_top_customer'),
            'currency' => app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol()
        ];
    }
}
