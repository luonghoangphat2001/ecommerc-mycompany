<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EDITOR = 'editor';
    case CSKH = 'cskh';
    case WAREHOUSE_STAFF = 'warehouse_staff';
    case ACCOUNTANT = 'accountant';
    case VIEWER = 'viewer';
    case REGISTERED_USER = 'Thành viên đăng ký';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::EDITOR => 'Editor',
            self::CSKH => 'Customer Support',
            self::WAREHOUSE_STAFF => 'Warehouse Staff',
            self::ACCOUNTANT => 'Accountant',
            self::VIEWER => 'Viewer',
            self::REGISTERED_USER => 'Registered User',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin (Quản trị tối cao)',
            self::ADMIN => 'Admin (Quản trị viên)',
            self::MANAGER => 'Quản lý vận hành (Manager)',
            self::EDITOR => 'Biên tập viên nội dung (Editor)',
            self::CSKH => 'Chăm sóc khách hàng (CSKH)',
            self::WAREHOUSE_STAFF => 'Nhân viên quản lý kho (Warehouse)',
            self::ACCOUNTANT => 'Kế toán & Tài chính (Accountant)',
            self::VIEWER => 'Người xem (Viewer)',
            self::REGISTERED_USER => 'Thành viên đăng ký (Khách hàng)',
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
