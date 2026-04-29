<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use App\Filament\Resources\UpsellProductResource;
use App\Filament\Resources\CrossSellProductResource;
use App\Filament\Resources\ComboProductResource;

class MarketingTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.marketing.label'))
            ->label(trans('admin.marketing.label'))
            ->icon('heroicon-o-megaphone')
            ->schema([
                Section::make(trans('admin.marketing.settings'))
                    ->schema([
                        Toggle::make('marketing.upsell_enabled')
                            ->label(trans('admin.marketing.upsell')),
                        Toggle::make('marketing.cross_sell_enabled')
                            ->label(trans('admin.marketing.cross_sell')),
                        Toggle::make('marketing.combo_enabled')
                            ->label(trans('admin.marketing.combo')),
                    ]),
                Section::make(trans('admin.marketing.management'))
                    ->hidden(fn (Get $get) =>
                        ! $get('marketing.upsell_enabled')
                        && ! $get('marketing.cross_sell_enabled')
                        && ! $get('marketing.combo_enabled')
                    )
                    ->schema([
                        Actions::make([
                            Action::make('manage_upsell')
                                ->label(trans('admin.marketing.upsell'))
                                ->url(fn() => UpsellProductResource::getUrl('index'))
                                ->icon('heroicon-o-arrow-up-circle')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('marketing.upsell_enabled')),
                            Action::make('manage_cross_sell')
                                ->label(trans('admin.marketing.cross_sell'))
                                ->url(fn() => CrossSellProductResource::getUrl('index'))
                                ->icon('heroicon-o-arrows-right-left')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('marketing.cross_sell_enabled')),
                            Action::make('manage_combo')
                                ->label(trans('admin.marketing.combo'))
                                ->url(fn() => ComboProductResource::getUrl('index'))
                                ->icon('heroicon-o-gift')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('marketing.combo_enabled')),
                        ])->fullWidth(),
                    ]),
            ]);
    }
}
