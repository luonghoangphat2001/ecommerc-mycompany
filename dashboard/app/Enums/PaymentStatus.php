<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Completed = 'completed';
    case Success = 'success';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('admin.order.payment_statuses.pending') ?? 'Pending',
            self::Paid => __('admin.order.payment_statuses.paid') ?? 'Paid',
            self::Completed, self::Success => __('admin.order.payment_statuses.completed') ?? 'Completed',
            self::Failed => __('admin.order.payment_statuses.failed') ?? 'Failed',
            self::Refunded => __('admin.order.payment_statuses.refunded') ?? 'Refunded',
        };
    }
}
