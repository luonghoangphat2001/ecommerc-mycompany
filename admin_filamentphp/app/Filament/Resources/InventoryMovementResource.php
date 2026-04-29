<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Filament\Resources\InventoryMovementResource\RelationManagers;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterResource(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('shop_product_id')
                    ->relationship('product', 'name')
                    ->label(trans('admin.product.label'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label(trans('admin.inventory.label'))
                    ->required(),
                Forms\Components\TextInput::make('quantity_changed')
                    ->label(trans('admin.inventory.qty_changed'))
                    ->numeric()
                    ->required()
                    ->helperText('Use positive numbers to ADD stock, negative to DEDUCT.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(trans('admin.product.label'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label(trans('admin.inventory.label'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('prev_stock')
                    ->label(trans('admin.inventory.prev_stock')),
                Tables\Columns\TextColumn::make('quantity_changed')
                    ->label(trans('admin.inventory.qty_changed'))
                    ->badge()
                    ->color(fn(int $state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('new_stock')
                    ->label(trans('admin.inventory.new_stock')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label(trans('admin.inventory.label')),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
        ];
    }
}




