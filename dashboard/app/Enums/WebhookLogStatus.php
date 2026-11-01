<?php

namespace App\Enums;

enum WebhookLogStatus: string
{
    case Pending = 'pending';
    case PendingApproval = 'pending_approval';
    case Processing = 'processing';
    case Success = 'success';
    case Failed = 'failed';

    /**
     * Default label (English).
     */
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

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xử lý',
            self::PendingApproval => 'Chờ phê duyệt',
            self::Processing => 'Đang xử lý',
            self::Success => 'Thành công',
            self::Failed => 'Thất bại',
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
