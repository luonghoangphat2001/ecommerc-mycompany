<?php

namespace App\Enums;

enum RefundType: string
{
    case Full = 'full';
    case Partial = 'partial';

    public function label(): string
    {
        return match ($this) {
            self::Full => __('admin.order.refund_types.full') ?? 'Full',
            self::Partial => __('admin.order.refund_types.partial') ?? 'Partial',
        };
    }
}
