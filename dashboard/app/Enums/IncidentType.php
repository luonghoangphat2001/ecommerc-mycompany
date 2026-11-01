<?php

namespace App\Enums;

enum IncidentType: string
{
    case LateDelivery = 'late_delivery';
    case DamagedGoods = 'damaged_goods';
    case ColdStorageAlarm = 'cold_storage_alarm';
    case SystemError = 'system_error';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::LateDelivery => 'Late Delivery',
            self::DamagedGoods => 'Damaged Goods',
            self::ColdStorageAlarm => 'Cold Storage Alarm',
            self::SystemError => 'System Error',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::LateDelivery => 'Giao hàng trễ',
            self::DamagedGoods => 'Hàng hư hỏng',
            self::ColdStorageAlarm => 'Cảnh báo kho lạnh',
            self::SystemError => 'Lỗi hệ thống',
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
