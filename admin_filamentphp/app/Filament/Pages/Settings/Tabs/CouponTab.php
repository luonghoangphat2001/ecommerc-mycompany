<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

class CouponTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.coupon.settings'))
            ->label(trans('admin.coupon.settings'))
            ->icon('heroicon-o-ticket')
            ->schema([
                Section::make(trans('admin.coupon.general_rules'))
                    ->schema([
                        Toggle::make('coupon.enable_coupons')
                            ->label(trans('admin.coupon.enable_coupons')),
                        Toggle::make('coupon.allow_multiple_coupons')
                            ->label(trans('admin.coupon.allow_multiple_coupons')),
                        Toggle::make('coupon.calculate_tax_after_coupon')
                            ->label(trans('admin.coupon.calculate_tax_after_coupon')),
                    ]),
            ]);
    }
}
