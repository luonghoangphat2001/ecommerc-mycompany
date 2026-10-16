<?php

namespace App\Enums;

enum MetricPeriod: string
{
    case Today = 'today';
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Today => 'Today',
            self::Month => 'This Month',
            self::Quarter => 'This Quarter',
            self::Year => 'This Year',
        };
    }
}
