<?php

namespace App\Traits;

use App\Settings\DBSettings;

trait HasCurrencyFormat
{

    public static function formatMoney_not_symbol($value): string
    {
        return app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->format($value, false);
    }

    public static function formatMoney($value): string
    {
        return app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->format($value, true);
    }
}
