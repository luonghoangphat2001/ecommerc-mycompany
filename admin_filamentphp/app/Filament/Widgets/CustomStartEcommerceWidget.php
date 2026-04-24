<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CustomStartEcommerceWidget extends Widget
{
    protected static string $view = 'admin.widgets.custom-start-ecommerce-widget';

    protected static ?int $sort = 0;

    public function getColumnSpan(): int|string|array
    {
        return 12; // Chiếm 7 cột trong Dashboard
    }

    public function getViewData(): array
    {
        $analyticsService = app(\App\Contracts\Services\AnalyticsServiceInterface::class);
        return $analyticsService->getStatsSummary();
    }
}
