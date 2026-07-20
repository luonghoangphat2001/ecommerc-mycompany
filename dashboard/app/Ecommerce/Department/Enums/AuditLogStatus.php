<?php

namespace App\Ecommerce\Department\Enums;

enum AuditLogStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case PENDING_APPROVAL = 'pending_approval';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::REJECTED => 'Rejected',
        };
    }
}
