<?php

namespace App\Enums;

enum MetricPeriod: string
{
    case Today = 'today';
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Today => 'Today',
            self::Month => 'This Month',
            self::Quarter => 'This Quarter',
            self::Year => 'This Year',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Today => 'Hôm nay',
            self::Month => 'Tháng này',
            self::Quarter => 'Quý này',
            self::Year => 'Năm nay',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function optionsVi(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->labelVi();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
