<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Set;
use App\Ecommerce\Settings\Contracts\ShopSettingServiceInterface;

class GeneralTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.shop.settings.general'))
            ->label(trans('admin.shop.settings.general'))
            ->icon('heroicon-o-information-circle')
            ->schema([
                Section::make(trans('admin.shop.settings.store_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('general.store_name')
                                    ->label(trans('admin.name'))
                                    ->required(),
                                TextInput::make('general.store_email')
                                    ->label(trans('admin.email'))
                                    ->email(),
                                TextInput::make('general.store_phone')
                                    ->label(trans('admin.phone')),
                                FileUpload::make('general.logo')
                                    ->label(trans('admin.logo'))
                                    ->image()
                                    ->directory('settings'),
                                FileUpload::make('general.favicon')
                                    ->label(trans('admin.logo_favicon'))
                                    ->image()
                                    ->directory('settings'),
                            ]),
                    ]),

                Section::make(trans('admin.shop.settings.localization'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('general.store_country')
                                    ->label(trans('admin.country'))
                                    ->options(config('countries'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (!$state) return;
                                        $info = app(ShopSettingServiceInterface::class)->getLocalizationInfo($state);
                                        if ($info) {
                                            $set('general.default_currency', $info['default_currency']);
                                            $set('general.currency_symbol', $info['currency_symbol']);
                                            $set('general.currency_position', $info['currency_position']);
                                            $set('general.thousand_separator', $info['thousand_separator']);
                                            $set('general.decimal_separator', $info['decimal_separator']);
                                            $set('general.decimal_places', $info['decimal_places']);
                                        }
                                    }),
                                Select::make('general.default_currency')
                                    ->label(trans('admin.shop.settings.currency'))
                                    ->options(config('locale-info.currencies'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('general.currency_symbol')
                                    ->label(trans('admin.shop.settings.currency_symbol'))
                                    ->required(),
                                Select::make('general.currency_position')
                                    ->label(trans('admin.shop.settings.currency_position'))
                                    ->options(trans('admin.shop.settings.position_options'))
                                    ->required(),
                                TextInput::make('general.decimal_places')
                                    ->label(trans('admin.shop.settings.decimal_places'))
                                    ->numeric()
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('general.thousand_separator')
                                    ->label(trans('admin.shop.settings.thousand_separator'))
                                    ->required(),
                                TextInput::make('general.decimal_separator')
                                    ->label(trans('admin.shop.settings.decimal_separator'))
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('general.weight_unit')
                                    ->label(trans('admin.settings.weight_unit'))
                                    ->options(config('units.weight', ['kg' => 'Kilogram (kg)', 'g' => 'Gram (g)', 'lb' => 'Pound (lb)'])),
                                Select::make('general.dimension_unit')
                                    ->label(trans('admin.settings.dimension_unit'))
                                    ->options(config('units.dimension', ['cm' => 'Centimeter (cm)', 'm' => 'Meter (m)', 'in' => 'Inch (in)'])),
                            ]),
                    ]),
            ]);
    }
}
