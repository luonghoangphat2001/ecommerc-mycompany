<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryRecordResource\Pages;
use App\Filament\Resources\InventoryRecordResource\RelationManagers;
use App\Models\InventoryRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryRecordResource extends Resource
{
    protected static ?string $model = InventoryRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterResource(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(trans('admin.general'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label(trans('admin.type'))
                                    ->options([
                                        'IN' => trans('admin.inventory.in'),
                                        'OUT' => trans('admin.inventory.out'),
                                        'TRANSFER' => trans('admin.inventory.transfer'),
                                    ])
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label(trans('admin.status'))
                                    ->options([
                                        'DRAFT' => trans('admin.inventory.draft'),
                                        'COMPLETED' => trans('admin.inventory.completed'),
                                    ])
                                    ->default('DRAFT')
                                    ->disabled()
                                    ->required(),

                            ]),
                        Forms\Components\Textarea::make('notes')
                            ->label(trans('admin.notes'))
                            ->rows(3),
                    ]),

                Forms\Components\Section::make(trans('admin.items'))
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('shop_product_id')
                                    ->label(trans('admin.product.label'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('warehouse_id')
                                    ->label(trans('admin.inventory.label'))
                                    ->relationship('warehouse', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label(trans('admin.qty'))
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(trans('admin.id'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(trans('admin.type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label(trans('admin.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'IN' => trans('admin.inventory.in'),
                        'OUT' => trans('admin.inventory.out'),
                        'TRANSFER' => trans('admin.inventory.transfer'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('process')
                    ->label(trans('admin.process'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'DRAFT')
                    ->action(fn ($record) => app(\App\Ecommerce\Inventory\Services\InventoryService::class)->processRecord($record->id)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryRecords::route('/'),
            'create' => Pages\CreateInventoryRecord::route('/create'),
            'edit' => Pages\EditInventoryRecord::route('/{record}/edit'),
        ];
    }
}

