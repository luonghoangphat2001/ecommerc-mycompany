<?php

namespace App\Filament\Resources;

// use App\Filament\Resources\Products;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\ProductResource\Widgets\ProductStats;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Resources\Concerns\Translatable;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Awcodes\Curator\Components\Infolists\CuratorEntry;
use Awcodes\Curator\Models\Media;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Filament\Resources\ProductResource\Pages;
use App\Traits\HasCurrencyFormat;
use App\Filament\Resources\ProductResource\RelationManagers\InventoryRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CommentsRelationManager;


class ProductResource extends Resource implements HasShieldPermissions
{
    use HasCurrencyFormat;

    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return trans('admin.product.label'); // Dịch "Category"
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',

        ];
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->label(trans('admin.name'))
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                                if ($operation !== 'create') {
                                                    return;
                                                }
                                                $set('slug', Str::slug($state));
                                            }),
                                        Forms\Components\TextInput::make('slug')
                                            ->disabled()
                                            ->dehydrated()
                                            ->label(trans('admin.slug'))
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(Product::class, 'slug', ignoreRecord: true),
                                    ]),
                                Forms\Components\MarkdownEditor::make('description')
                                    ->columnSpan('full')
                                    ->label(trans('admin.description')),
                            ])
                            ->icon('heroicon-o-document-text'),

                        Forms\Components\Section::make(trans('admin.product.pricing'))
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->numeric()
                                            ->label(trans('admin.price'))
                                            ->prefix(app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol())
                                            ->required(),
                                        Forms\Components\TextInput::make('old_price')
                                            ->numeric()
                                            ->label(trans('admin.old_price'))
                                            ->prefix(app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol())
                                            ->required(),
                                        Forms\Components\TextInput::make('cost')
                                            ->label(trans('admin.cost'))
                                            ->helperText(trans('admin.product.cost_helper'))
                                            ->numeric()
                                            ->prefix(app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol())
                                            ->required(),
                                    ]),
                            ])
                            ->icon('heroicon-o-currency-dollar'),
                        Forms\Components\Section::make(trans('admin.product.inventory'))
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('sku')
                                            ->label(trans('admin.sku_label'))
                                            ->unique(Product::class, 'sku', ignoreRecord: true)
                                            ->required(),
                                        Forms\Components\TextInput::make('barcode')
                                            ->label(trans('admin.barcode'))
                                            ->unique(Product::class, 'barcode', ignoreRecord: true)
                                            ->required(),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('qty')
                                            ->label(trans('admin.qty'))
                                            ->numeric()
                                            ->required(),
                                        Forms\Components\TextInput::make('security_stock')
                                            ->label(trans('admin.inventory.low_stock_threshold'))
                                            ->helperText(trans('admin.product.security_stock_helper'))
                                            ->numeric()
                                            ->required(),
                                    ]),
                            ])
                            ->icon('heroicon-o-briefcase'),

                        // Forms\Components\Section::make('Shipping')
                        //     ->schema([
                        //         Forms\Components\Checkbox::make('backorder')
                        //             ->label(trans('admin.backorder')),

                        //         Forms\Components\Checkbox::make('requires_shipping')
                        //             ->label(trans('admin.requires_shipping')),
                        //     ])
                        //     ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(trans('admin.status'))
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label(trans('admin.is_visible'))
                                    ->default(true),

                                Forms\Components\DatePicker::make('published_at')
                                    ->label(trans('admin.published_date'))
                                    ->default(now())
                                    ->required(),
                            ])
                            ->icon('heroicon-o-check-circle'),

                        Forms\Components\Section::make(trans('admin.associations'))
                            ->schema([
                                Forms\Components\Select::make('shop_brand_id')
                                    ->relationship('brands', 'name')
                                    ->label(trans('admin.brand.label'))
                                    ->multiple(),

                                Forms\Components\Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->label(trans('admin.category.label'))
                                    ->multiple()
                                    ->required(),
                            ])
                            ->icon('heroicon-o-rectangle-stack'),
                        Forms\Components\Section::make(trans('admin.image'))
                            ->schema([
                                CuratorPicker::make('product_images')
                                    ->label(trans('admin.add_image'))
                                    ->size(10)
                                    ->extraAttributes([
                                        'class' => 'custom-curator-height',
                                    ])
                            ])
                            ->icon('heroicon-o-photo')
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\SpatieMediaLibraryImageColumn::make('product-image')
                //     ->label('Image')
                //     ->collection('product-images'),
                CuratorColumn::make('product_images')
                    ->label(trans('admin.product.product_images'))
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('admin.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('categories.name')
                    ->label(trans('admin.category.label'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('brands.name')
                    ->label(trans('admin.brand.short_name'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),


                Tables\Columns\TextColumn::make('price')
                    ->label(trans('admin.price'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => self::formatMoney($state)),

                Tables\Columns\TextColumn::make('sku')
                    ->label(trans('admin.sku'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('qty')
                    ->label(trans('admin.qty'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('security_stock')
                    ->label(trans('admin.inventory.low_stock_threshold'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),


                Tables\Columns\IconColumn::make('is_visible')
                    ->label(trans('admin.is_visible'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(trans('admin.published_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters(
                [
                    QueryBuilder::make()
                        ->constraints([
                            TextConstraint::make('name'),
                            TextConstraint::make('slug'),
                            TextConstraint::make('sku')
                                ->label(trans('admin.sku_label')),
                            TextConstraint::make('barcode')
                                ->label('Barcode (ISBN, UPC, GTIN, etc.)'),
                            TextConstraint::make('description'),
                            NumberConstraint::make('old_price')
                                ->label('Compare at price')
                                ->icon('heroicon-m-currency-dollar'),
                            NumberConstraint::make('price')
                                ->icon('heroicon-m-currency-dollar'),
                            NumberConstraint::make('cost')
                                ->label('Cost per item')
                                ->icon('heroicon-m-currency-dollar'),
                            NumberConstraint::make('qty')
                                ->label('Quantity'),
                            NumberConstraint::make('security_stock'),
                            BooleanConstraint::make('is_visible')
                                ->label('Visibility'),
                            BooleanConstraint::make('featured'),
                            BooleanConstraint::make('backorder'),
                            BooleanConstraint::make('requires_shipping')
                                ->icon('heroicon-m-truck'),
                            DateConstraint::make('published_at'),
                        ])
                        ->constraintPickerColumns(2),
                ],
                // layout: Tables\Enums\FiltersLayout::AboveContentCollapsible
            )
            ->deferFilters()
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            // ->headerActions([ // <-- Thêm đoạn này để có nút Create trên danh sách sản phẩm
            //     Tables\Actions\CreateAction::make()->label(trans('admin.create')),
            // ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()

            ]);
    }

    public static function getRelations(): array
    {
        return [
            InventoryRelationManager::class,
            CommentsRelationManager::class,
        ];
    }


    public static function getWidgets(): array
    {
        return [
            ProductStats::class,
        ];
    }

    public static function getPages(): array
    {

        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'brand.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Product $record */

        return [
            'Brand' => optional($record->brand)->name,
        ];
    }

    /** @return Builder<Product> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['brand']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class)->getLowStockCount();
    }

    /** @return Builder<Product> */
    public static function getEloquentQuery(): Builder
    {
        return app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class)->getTableQuery();
    }
}
