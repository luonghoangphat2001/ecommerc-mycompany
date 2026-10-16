<?php

namespace App\Enums;

enum LoyaltyActionType: string
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Earn => 'Earn Points',
            self::Redeem => 'Redeem Points',
            self::Refund => 'Points Refunded',
        };
    }
}
