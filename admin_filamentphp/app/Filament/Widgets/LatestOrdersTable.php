<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Traits\HasCurrencyFormat;

class LatestOrdersTable extends BaseWidget
{
    use HasCurrencyFormat;

    public function getColumnSpan(): int | string | array
    {
        return [
            'xl' => 12,
            'md' => 12,
        ];
    }

    protected static ?string $heading = null;

    public function __construct()
    {
        static::$heading = trans('admin.chart.last_order');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getTableQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(trans('admin.created_at'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->label(trans('admin.order.number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_display_name')
                    ->label(trans('admin.order.customer'))
                    ->searchable(['shippingAddress.first_name', 'shippingAddress.last_name', 'shippingAddress.email'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(trans('admin.order.status'))
                    ->badge(),
                Tables\Columns\TextColumn::make('total')
                    ->label(trans('admin.order.total_price'))
                    ->formatStateUsing(fn($state) => self::formatMoney($state))
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make(trans('admin.open'))
                    ->url(fn(Order $record): string => OrderResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
