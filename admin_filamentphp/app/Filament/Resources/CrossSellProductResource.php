<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrossSellProductResource\Pages;
use App\Models\CrossSellProduct;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CrossSellProductResource extends Resource
{
    protected static ?string $model = CrossSellProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Marketing';

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
                Forms\Components\Select::make('shop_product_id')
                    ->label(trans('admin.product.label'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('cross_sell_product_id')
                    ->label(trans('admin.marketing.cross_sell_product'))
                    ->relationship('crossSellProduct', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->label(trans('admin.sort_order'))
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label(trans('admin.is_active'))
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(trans('admin.product.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('crossSellProduct.name')
                    ->label(trans('admin.marketing.cross_sell_product'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(trans('admin.sort_order'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(trans('admin.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shop_product_id')
                    ->label(trans('admin.product.label'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(trans('admin.is_active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrossSellProducts::route('/'),
            'create' => Pages\CreateCrossSellProduct::route('/create'),
            'edit' => Pages\EditCrossSellProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin.marketing.cross_sell');
    }

    public static function getModelLabel(): string
    {
        return trans('admin.marketing.cross_sell');
    }
}
