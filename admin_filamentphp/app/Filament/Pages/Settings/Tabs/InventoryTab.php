<?php

namespace App\Filament\Pages\Settings\Tabs;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\InventoryRecordResource;
use App\Filament\Resources\InventoryMovementResource;

class InventoryTab
{
    public static function make(): Tab
    {
        return Tab::make(trans('admin.inventory.label'))
            ->label(trans('admin.inventory.label'))
            ->icon('heroicon-o-archive-box')
            ->schema([
                Section::make(trans('admin.inventory.settings'))
                    ->schema([
                        Toggle::make('inventory.multi_warehouse_enabled')
                            ->label(trans('admin.inventory.multi_warehouse_enabled')),
                        Toggle::make('inventory.split_shipping_enabled')
                            ->label(trans('admin.inventory.split_shipping_enabled')),
                        Grid::make(2)
                            ->schema([
                                Select::make('inventory.warehouse_selection_strategy')
                                    ->label(trans('admin.inventory.warehouse_selection_strategy'))
                                    ->options([
                                        'stock_volume' => trans('admin.inventory.low_stock_threshold'),
                                        'proximity' => trans('admin.inventory.strategy_proximity'),
                                    ])
                                    ->required(),
                                TextInput::make('inventory.reservation_expiry_minutes')
                                    ->label(trans('admin.inventory.reservation_expiry_minutes'))
                                    ->numeric()
                                    ->required(),
                            ]),
                    ]),
                Section::make(trans('admin.inventory.management'))
                    ->hidden(fn (Get $get) => ! $get('inventory.multi_warehouse_enabled'))
                    ->schema([
                        Actions::make([
                            Action::make('manage_inventories')
                                ->label(trans('admin.inventory.label'))
                                ->url(fn() => InventoryResource::getUrl('index'))
                                ->icon('heroicon-o-archive-box')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('inventory.multi_warehouse_enabled')),
                            Action::make('manage_inventory_records')
                                ->label(trans('admin.inventory.records'))
                                ->url(fn() => InventoryRecordResource::getUrl('index'))
                                ->icon('heroicon-o-document-text')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('inventory.multi_warehouse_enabled')),
                            Action::make('manage_inventory_movements')
                                ->label(trans('admin.inventory.movements'))
                                ->url(fn() => InventoryMovementResource::getUrl('index'))
                                ->icon('heroicon-o-arrow-path')
                                ->color('primary')
                                ->hidden(fn (Get $get) => ! $get('inventory.multi_warehouse_enabled')),
                        ])->fullWidth(),
                    ]),
            ]);
    }
}
