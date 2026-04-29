<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return trans('admin.coupon.label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Coupon Tabs')
                    ->tabs([
                        // 1. General Tab
                        Forms\Components\Tabs\Tab::make(trans('admin.coupon.tabs.general'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label(trans('admin.coupon.code'))
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                Forms\Components\Select::make('type')
                                    ->label(trans('admin.coupon.type'))
                                    ->options([
                                        'fixed_cart' => trans('admin.coupon.types.fixed_cart'),
                                        'percentage' => trans('admin.coupon.types.percentage'),
                                        'fixed_product' => trans('admin.coupon.types.fixed_product'),
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('amount')
                                    ->label(trans('admin.coupon.amount'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),

                                Forms\Components\DateTimePicker::make('expiry_date')
                                    ->label(trans('admin.coupon.expiry_date')),

                                Forms\Components\Toggle::make('is_active')
                                    ->label(trans('admin.coupon.is_active'))
                                    ->default(true),
                            ]),

                        // 2. Usage Restriction Tab
                        Forms\Components\Tabs\Tab::make(trans('admin.coupon.tabs.restriction'))
                            ->icon('heroicon-o-exclamation-triangle')
                            ->schema([
                                Forms\Components\TextInput::make('minimum_order_amount')
                                    ->label(trans('admin.coupon.minimum_order_amount'))
                                    ->numeric()
                                    ->minValue(0),

                                Forms\Components\Toggle::make('individual_use')
                                    ->label(trans('admin.coupon.individual_use'))
                                    ->default(false),

                                Forms\Components\Toggle::make('exclude_sale_items')
                                    ->label(trans('admin.coupon.exclude_sale_items'))
                                    ->default(false),

                                Forms\Components\Select::make('product_ids')
                                    ->label(trans('admin.coupon.product_ids'))
                                    ->options(fn() => Product::all()->mapWithKeys(fn($p) => [$p->id => $p->name])->toArray())
                                    ->multiple()
                                    ->searchable(),

                                Forms\Components\Select::make('excluded_product_ids')
                                    ->label(trans('admin.coupon.excluded_product_ids'))
                                    ->options(fn() => Product::all()->mapWithKeys(fn($p) => [$p->id => $p->name])->toArray())
                                    ->multiple()
                                    ->searchable(),

                                Forms\Components\Select::make('category_ids')
                                    ->label(trans('admin.coupon.category_ids'))
                                    ->options(fn() => ProductCategory::all()->mapWithKeys(fn($c) => [$c->id => $c->name])->toArray())
                                    ->multiple()
                                    ->searchable(),

                                Forms\Components\Select::make('excluded_category_ids')
                                    ->label(trans('admin.coupon.excluded_category_ids'))
                                    ->options(fn() => ProductCategory::all()->mapWithKeys(fn($c) => [$c->id => $c->name])->toArray())
                                    ->multiple()
                                    ->searchable(),
                            ]),

                        // 3. Usage Limits Tab
                        Forms\Components\Tabs\Tab::make(trans('admin.coupon.tabs.limits'))
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Forms\Components\TextInput::make('usage_limit')
                                    ->label(trans('admin.coupon.usage_limit'))
                                    ->numeric()
                                    ->minValue(0),

                                Forms\Components\TextInput::make('usage_limit_per_user')
                                    ->label(trans('admin.coupon.usage_limit_per_user'))
                                    ->numeric()
                                    ->minValue(0),

                                Forms\Components\TextInput::make('limit_usage_to_x_items')
                                    ->label(trans('admin.coupon.limit_usage_to_x_items'))
                                    ->numeric()
                                    ->minValue(0),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(trans('admin.coupon.code'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(trans('admin.coupon.type'))
                    ->formatStateUsing(fn($state) => trans("admin.coupon.types.{$state}"))
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(trans('admin.coupon.amount'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage_count')
                    ->label(trans('admin.coupon.usage_count'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label(trans('admin.coupon.expiry_date'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(trans('admin.coupon.is_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
