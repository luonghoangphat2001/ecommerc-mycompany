<?php

namespace App\Ecommerce\Order\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case New = 'new';
    case Processing = 'processing';
    case Delivering = 'delivering';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::New => 'New',
            self::Processing => 'Processing',
            self::Delivering => 'Delivering',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    /**
     * Filament / backward compatibility label method.
     */
    public function getLabel(): string
    {
        return $this->label();
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xử lý',
            self::New => 'Mới',
            self::Processing => 'Đang xử lý',
            self::Delivering => 'Đang giao hàng',
            self::Completed => 'Hoàn tất',
            self::Cancelled => 'Đã hủy',
            self::Refunded => 'Đã hoàn tiền',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::New => 'info',
            self::Processing => 'warning',
            self::Delivering => 'info',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::Refunded => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending => 'heroicon-m-clock',
            self::New => 'heroicon-m-sparkles',
            self::Processing => 'heroicon-m-arrow-path',
            self::Delivering => 'heroicon-m-truck',
            self::Completed => 'heroicon-m-check-badge',
            self::Cancelled => 'heroicon-m-x-circle',
            self::Refunded => 'heroicon-m-arrow-uturn-left',
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
