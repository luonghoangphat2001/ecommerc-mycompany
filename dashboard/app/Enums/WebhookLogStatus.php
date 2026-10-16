<?php

namespace App\Enums;

enum WebhookLogStatus: string
{
    case Pending = 'pending';
    case PendingApproval = 'pending_approval';
    case Processing = 'processing';
    case Success = 'success';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::PendingApproval => 'Pending Approval',
            self::Processing => 'Processing',
            self::Success => 'Success',
            self::Failed => 'Failed',
        };
    }
}
