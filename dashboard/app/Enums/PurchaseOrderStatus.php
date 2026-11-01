<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Shipping = 'shipping';
    case Partial = 'partial';
    case Completed = 'completed';
    case DefectiveReturn = 'defective_return';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Shipping => 'Shipping',
            self::Partial => 'Partially Delivered',
            self::Completed => 'Completed',
            self::DefectiveReturn => 'Defective Return',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Shipping => 'Đang giao',
            self::Partial => 'Giao 1 phần',
            self::Completed => 'Hoàn thành',
            self::DefectiveReturn => 'Trả hàng lỗi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Shipping => 'info',
            self::Partial => 'warning',
            self::Completed => 'success',
            self::DefectiveReturn => 'danger',
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
