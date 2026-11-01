<?php

namespace App\Enums;

enum LoyaltyActionType: string
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Refund = 'refund';
    case Adjustment = 'adjustment';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Earn => 'Earn Points',
            self::Redeem => 'Redeem Points',
            self::Refund => 'Points Refunded',
            self::Adjustment => 'Points Adjusted',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Earn => 'Tích điểm',
            self::Redeem => 'Đổi điểm',
            self::Refund => 'Hoàn điểm',
            self::Adjustment => 'Điều chỉnh điểm',
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
