<?php

namespace App\Filament\Resources;

use App\Ecommerce\Order\Enums\OrderStatus;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;

use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Squire\Models\Currency;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use App\Traits\HasCurrencyFormat;

class OrderResource extends Resource implements HasShieldPermissions
{
    use HasCurrencyFormat;

    protected static ?string $model = Order::class;

    protected static ?string $slug = 'orders';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 5;
    protected static bool $canGloballySearch = false;
    public static function getModelLabel(): string
    {
        return trans('admin.order.label');
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
                // CỘT CHÍNH (TRÁI - 2/3)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(trans('admin.order.contact_info'))
                            ->description(trans('admin.order.contact_info_desc'))
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->relationship('shippingAddress')
                                    ->schema([
                                        Forms\Components\TextInput::make('first_name')
                                            ->label(trans('admin.first_name'))
                                            ->required(),

                                        Forms\Components\TextInput::make('last_name')
                                            ->label(trans('admin.last_name'))
                                            ->required(),

                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),

                                        Forms\Components\TextInput::make('phone')
                                            ->label(trans('admin.phone'))
                                            ->required(),
                                    ]),
                            ])
                            ->collapsible()
                            ->disabled(fn(?Order $record) => $record && !in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])),

                        Forms\Components\Section::make(trans('admin.order.details'))
                            ->headerActions([
                                Action::make('reset')
                                    ->label(trans('admin.edit'))
                                    ->modalHeading(trans('admin.order.reset_items'))
                                    ->modalDescription(trans('admin.order.reset_items_desc'))
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn(Forms\Set $set) => $set('items', []))
                                    ->hidden(fn(?Order $record) => $record && !in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])),
                            ])
                            ->schema([
                                static::getItemsRepeater(),
                            ])
                            ->disabled(fn(?Order $record) => $record && !in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])),

                        Forms\Components\Section::make(trans('admin.order.shipping'))
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->relationship('shipping')
                                    ->schema([
                                        Forms\Components\Placeholder::make('method')
                                            ->label(trans('admin.order.shipping_method'))
                                            ->content(fn(?Model $record) => $record?->method),
                                        Forms\Components\Placeholder::make('amount')
                                            ->label(trans('admin.order.shipping_cost'))
                                            ->content(fn(?Model $record) => $record instanceof \App\Models\OrderShipping ? self::formatMoney(app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getTotalShipping($record->order)) : '0'),
                                        Forms\Components\TextInput::make('tax.amount')
                                            ->label(trans('admin.order.tax'))
                                            ->prefix(app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol())
                                            ->numeric()
                                            ->default(0)
                                            ->required(),
                                        Forms\Components\Placeholder::make('shipping_total')
                                            ->label(trans('admin.order.total_payment'))
                                            ->content(fn(?Model $record) => $record instanceof \App\Models\OrderShipping ? self::formatMoney(app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getShippingTotalWithTax($record->order)) : '0'),
                                    ]),
                            ])->collapsible(),

                        Forms\Components\Section::make(trans('admin.order.notes'))
                            ->schema([
                                Forms\Components\Repeater::make('metas')
                                    ->relationship('metas')
                                    ->schema([
                                        Forms\Components\TextInput::make('key')
                                            ->label(trans('admin.fields.key'))
                                            ->required()
                                            ->disabled(fn($state) => in_array($state, ['ip_address', 'user_agent', 'cancellation_reason']))
                                            ->columnSpan(1),
                                        Forms\Components\Textarea::make('value')
                                            ->label(trans('admin.fields.value'))
                                            ->rows(1)
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2)
                                    ->label(trans('admin.order.metadata_notes'))
                                    ->itemLabel(fn(array $state): ?string => $state['key'] ?? null)
                                    ->collapsible(),
                            ])
                            ->collapsible()
                            ->disabled(fn(?Order $record) => $record && !in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])),

                        Forms\Components\Section::make(trans('admin.order.payment'))
                            ->schema([
                                Forms\Components\Grid::make(5)
                                    ->schema([
                                        Forms\Components\Placeholder::make('subtotal')
                                            ->label(trans('admin.order.subtotal'))
                                            ->content(fn(?Order $record) => $record ? self::formatMoney($record->subtotal) : '0'),

                                        Forms\Components\Placeholder::make('shipping_price_display')
                                            ->label(trans('admin.order.shipping_cost'))
                                            ->content(fn(?Order $record) => $record ? self::formatMoney((float)app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getTotalShipping($record)) : '0'),

                                        Forms\Components\Placeholder::make('tax_price_display')
                                            ->label(trans('admin.order.total_tax_display'))
                                            ->content(fn(?Order $record) => $record ? self::formatMoney(app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getTaxTotal($record)) : '0'),

                                        Forms\Components\Placeholder::make('discount_display')
                                            ->label(trans('admin.coupon.discount'))
                                            ->content(function (?Order $record) {
                                                if (!$record) return '0';
                                                $coupon = $record->coupons()->first();
                                                if (!$coupon) return '0';
                                                return '-' . self::formatMoney((float)$coupon->discount_amount) . ' (' . $coupon->coupon_code . ')';
                                            }),

                                        Forms\Components\Placeholder::make('total_display')
                                            ->label(trans('admin.order.total_payment'))
                                            ->extraAttributes(['class' => 'font-bold text-primary-600 text-2xl'])
                                            ->content(fn(?Order $record) => $record ? self::formatMoney((float)$record->total) : '0'),
                                    ]),

                                Forms\Components\Hidden::make('total'),

                            ]),

                        Forms\Components\Section::make(trans('admin.order.refunds'))
                            ->visible(fn(?Order $record) => $record?->status === OrderStatus::Refunded)
                            ->schema([
                                Forms\Components\Repeater::make('refunds')
                                    ->relationship('refunds')
                                    ->schema([
                                        Forms\Components\TextInput::make('amount')
                                            ->numeric()
                                            ->disabled(),
                                        Forms\Components\Textarea::make('reason')
                                            ->disabled(),
                                    ])
                                    ->columns(2)
                                    ->addable(false)
                                    ->deletable(false),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // SIDEBAR (PHẢI - 1/3)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(trans('admin.order.status_management'))
                            ->schema([
                                Forms\Components\TextInput::make('number')
                                    ->default('OR-' . random_int(100000, 999999))
                                    ->disabled()
                                    ->label(trans('admin.order.number'))
                                    ->dehydrated()
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label(trans('admin.order.status'))
                                    ->options(OrderStatus::class)
                                    ->required()
                                    ->native(false)
                                    ->reactive(),

                                Forms\Components\Select::make('user_id')
                                    ->label(trans('admin.order.customer'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->disabled()
                                    ->placeholder(trans('admin.order.guest_customer')),

                                Forms\Components\TextInput::make('customer_type')
                                    ->label(trans('admin.order.customer_type'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn(?Order $record) => $record?->customer_type)
                                    ->suffixIcon('heroicon-m-user'),

                                Forms\Components\Placeholder::make('created_at')
                                    ->label(trans('admin.created_at'))
                                    ->content(fn(?Order $record): ?string => $record?->created_at?->toFormattedDateString()),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(trans('admin.updated_at'))
                                    ->content(fn(?Order $record): ?string => $record?->updated_at?->diffForHumans()),
                            ]),

                        Forms\Components\Section::make(trans('admin.order.shipping_address'))
                            ->schema([
                                Forms\Components\Group::make()
                                    ->relationship('shippingAddress')
                                    ->schema([
                                        \App\Forms\Components\AddressForm::make('shippingAddress_dummy')
                                            ->label('')
                                            ->dehydrated(false)
                                    ]),
                            ])
                            ->collapsible()
                            ->disabled(fn(?Order $record) => $record && !in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])),

                        Forms\Components\Section::make(trans('admin.order.billing_address'))
                            ->schema([
                                Forms\Components\Group::make()
                                    ->relationship('billingAddress')
                                    ->schema([
                                        \App\Forms\Components\AddressForm::make('billingAddress_dummy')
                                            ->label('')
                                            ->dehydrated(false)
                                    ]),
                            ])
                            ->collapsible()
                            ->disabled(fn(?Order $record) => $record && !in_array($record->status, [OrderStatus::Pending, OrderStatus::Processing])),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->label(trans('admin.order.number'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_display_name')
                    ->label(trans('admin.order.customer'))
                    ->searchable(['shippingAddress.first_name', 'shippingAddress.last_name'])
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customer_type')
                    ->label(trans('admin.order.customer_type'))
                    ->badge()
                    ->color(fn(Order $record): string => $record->user_id ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label(trans('admin.order.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->searchable()
                    ->sortable()
                    ->label(trans('admin.order.total_price'))
                    ->formatStateUsing(fn($state) => self::formatMoney($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.order.created_at'))
                    ->date()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(trans('admin.start_date')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(trans('admin.end_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->createdAtBetween($data['created_from'], $data['created_until']);
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('process')
                        ->label(trans('admin.order.process'))
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->action(fn(Order $record) => app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->updateStatus($record, OrderStatus::Processing))
                        ->visible(fn(Order $record) => $record->status === OrderStatus::Pending || $record->status === OrderStatus::New),
                    Tables\Actions\Action::make('ship')
                        ->label(trans('admin.order.ship'))
                        ->icon('heroicon-m-truck')
                        ->color('info')
                        ->action(fn(Order $record) => app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->updateStatus($record, OrderStatus::Delivering))
                        ->visible(fn(Order $record) => $record->status === OrderStatus::Processing),
                    Tables\Actions\Action::make('complete')
                        ->label(trans('admin.order.complete'))
                        ->icon('heroicon-m-check-badge')
                        ->color('success')
                        ->action(fn(Order $record) => app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->updateStatus($record, OrderStatus::Completed))
                        ->visible(fn(Order $record) => $record->status === OrderStatus::Delivering),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
            RelationManagers\RefundsRelationManager::class,
            RelationManagers\ActivityLogRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Order> */
    public static function getEloquentQuery(): Builder
    {
        return app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getTableQuery()
            ->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('productItems')
            ->relationship()
            ->label(trans('admin.order.items'))
            ->schema([
                Forms\Components\Select::make('shop_product_id')
                    ->label(trans('admin.product.label'))
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                        $orderType = $get('../../type');
                        $productService = app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class);

                        return $productService->searchByName($search, $orderType, 50);
                    })
                    ->getOptionLabelUsing(fn($value): ?string => app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class)->find((string)$value)?->name)
                    ->reactive()
                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('unit_price', app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class)->find($state)?->price ?? 0))
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan(['md' => 5]),

                Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->default(1)
                    ->label(trans('admin.qty'))
                    ->columnSpan(['md' => 2])
                    ->required(),

                Forms\Components\TextInput::make('unit_price')
                    ->label(trans('admin.unit_price'))
                    ->prefix(app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol())
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan(['md' => 2]),

                Forms\Components\TextInput::make('tax.amount')
                    ->label(trans('admin.order.tax'))
                    ->prefix(app(\App\Ecommerce\Core\Contracts\CurrencyServiceInterface::class)->getCurrencySymbol())
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->columnSpan(['md' => 3])
            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip(trans('admin.open_product'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);
                        $product = app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class)->find($itemData['shop_product_id']);
                        return $product ? ProductResource::getUrl('edit', ['record' => $product]) : null;
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn(array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['shop_product_id'])),
            ])
            ->orderColumn('sort')
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns(['md' => 12])
            ->required();
    }
}
