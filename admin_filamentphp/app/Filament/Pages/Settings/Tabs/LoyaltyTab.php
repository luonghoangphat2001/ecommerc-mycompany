<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use App\Filament\Resources\LoyaltyPointResource;
use App\Filament\Resources\LoyaltyLogResource;

class LoyaltyTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.loyalty.label'))
            ->label(trans('admin.loyalty.label'))
            ->icon('heroicon-o-gift')
            ->schema([
                Section::make(trans('admin.loyalty.settings'))
                    ->schema([
                        Toggle::make('loyalty.enabled')
                            ->label(trans('admin.loyalty.enabled')),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('loyalty.points_per_currency')
                                    ->label(trans('admin.loyalty.points_per_currency'))
                                    ->numeric()
                                    ->required(),
                                TextInput::make('loyalty.point_conversion_rate')
                                    ->label(trans('admin.loyalty.point_conversion_rate'))
                                    ->numeric()
                                    ->required(),
                            ]),
                    ]),
                Section::make(trans('admin.loyalty.management'))
                    ->hidden(fn (Get $get) => ! $get('loyalty.enabled'))
                    ->schema([
                        Actions::make([
                            Action::make('manage_loyalty_points')
                                ->label(trans('admin.loyalty.points'))
                                ->url(fn() => LoyaltyPointResource::getUrl('index'))
                                ->icon('heroicon-o-star')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('loyalty.enabled')),
                            Action::make('manage_loyalty_logs')
                                ->label(trans('admin.loyalty.logs'))
                                ->url(fn() => LoyaltyLogResource::getUrl('index'))
                                ->icon('heroicon-o-clock')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('loyalty.enabled')),
                        ])->fullWidth(),
                    ]),
            ]);
    }
}
