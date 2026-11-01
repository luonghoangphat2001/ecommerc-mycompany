<?php

namespace App\Ecommerce\Department\Enums;

enum AuditLogStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case PENDING_APPROVAL = 'pending_approval';
    case REJECTED = 'rejected';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::REJECTED => 'Rejected',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::SUCCESS => 'Thành công',
            self::FAILED => 'Thất bại',
            self::PENDING_APPROVAL => 'Chờ duyệt',
            self::REJECTED => 'Từ chối',
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
