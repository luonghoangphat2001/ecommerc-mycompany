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

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => trans('admin.pending'),
            self::New =>  trans('admin.new'),
            self::Processing => trans('admin.processing'),
            self::Delivering => trans('admin.delivering'),
            self::Completed =>  trans('admin.completed'),
            self::Cancelled =>  trans('admin.cancelled'),
            self::Refunded =>  trans('admin.refunded'),
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
}
