<?php

namespace App\Enums;

enum RefundType: string
{
    case Full = 'full';
    case Partial = 'partial';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Full => 'Full',
            self::Partial => 'Partial',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Full => 'Toàn phần',
            self::Partial => 'Một phần',
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
