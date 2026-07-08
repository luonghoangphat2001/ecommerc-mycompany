<?php

namespace App\Ecommerce\Core\Contracts;

interface CurrencyServiceInterface
{
    /**
     * Get the current currency symbol.
     *
     * @return string
     */
    public function getCurrencySymbol(): string;

    /**
     * Get the current currency code.
     *
     * @return string
     */
    public function getCurrencyCode(): string;

    /**
     * Format a price value with or without symbol.
     *
     * @param mixed $value
     * @param bool $withSymbol
     * @return string
     */
    public function format(mixed $value, bool $withSymbol = true): string;

    /**
     * Format a number without currency symbol.
     *
     * @param mixed $value
     * @param int|null $decimalPlaces
     * @return string
     */
    public function formatNumber(mixed $value, ?int $decimalPlaces = null): string;
}
