<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\ShippingZoneResource;

class ShippingTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.shop.settings.shipping'))
            ->label(trans('admin.shop.settings.shipping'))
            ->icon('heroicon-o-truck')
            ->schema([
                Placeholder::make('shipping_link')
                    ->label(trans('admin.shop.settings.shipping_zone_management'))
                    ->content(trans('admin.shop.settings.manage_shipping_zones_desc'))
                    ->hintAction(
                        Action::make('go_to_shipping')
                            ->label(trans('admin.shop.settings.go_to_shipping'))
                            ->url(fn() => ShippingZoneResource::getUrl('index'))
                            ->icon('heroicon-m-arrow-top-right-on-square')
                    ),
            ]);
    }
}
