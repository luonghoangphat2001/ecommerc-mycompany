<?php

namespace App\Ecommerce\Core\Services;

use App\Ecommerce\Core\Contracts\CurrencyServiceInterface;
use App\Settings\GeneralSettings;

class CurrencyService implements CurrencyServiceInterface
{
    /**
     * @var GeneralSettings
     */
    protected $settings;

    /**
     * CurrencyService constructor.
     */
    public function __construct()
    {
        $this->settings = app(GeneralSettings::class);
    }

    /**
     * @inheritDoc
     */
    public function getCurrencySymbol(): string
    {
        return $this->settings->currency_symbol ?? '₫';
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyCode(): string
    {
        return $this->settings->default_currency ?? 'VND';
    }

    /**
     * @inheritDoc
     */
    public function format(mixed $value, bool $withSymbol = true): string
    {
        $value = (float) $value;
        $decimalPlaces = $this->settings->decimal_places ?? 0;
        $thousandSep = $this->settings->thousand_separator ?? '.';
        $decimalSep = $this->settings->decimal_separator ?? ',';
        $position = $this->settings->currency_position ?? 'right_space';
        $symbol = $this->getCurrencySymbol();

        $formatted = number_format($value, $decimalPlaces, $decimalSep, $thousandSep);

        if (!$withSymbol) {
            return $formatted;
        }

        switch ($position) {
            case 'left':
                return $symbol . $formatted;
            case 'left_space':
                return $symbol . ' ' . $formatted;
            case 'right':
                return $formatted . $symbol;
            case 'right_space':
            default:
                return $formatted . ' ' . $symbol;
        }
    }

    /**
     * @inheritDoc
     */
    public function formatNumber(mixed $value, ?int $decimalPlaces = null): string
    {
        $value = (float) $value;
        $decimalPlaces = $decimalPlaces ?? ($this->settings->decimal_places ?? 0);
        $thousandSep = $this->settings->thousand_separator ?? '.';
        $decimalSep = $this->settings->decimal_separator ?? ',';
        
        return number_format($value, $decimalPlaces, $decimalSep, $thousandSep);
    }
}
