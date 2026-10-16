<?php

namespace App\Enums;

enum RefundStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('admin.order.refund_statuses.pending') ?? 'Pending',
            self::Completed => __('admin.order.refund_statuses.completed') ?? 'Completed',
            self::Failed => __('admin.order.refund_statuses.failed') ?? 'Failed',
        };
    }
}
