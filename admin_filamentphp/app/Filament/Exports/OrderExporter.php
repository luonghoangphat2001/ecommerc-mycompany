<?php

namespace App\Filament\Exports;

use App\Filament\Exports\BaseExporter;
use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;

class OrderExporter extends BaseExporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ...static::commonColumns(),
            ExportColumn::make('number'),
            ExportColumn::make('shippingAddress.email')
                ->label(trans('admin.email')),
            ExportColumn::make('shippingAddress.phone')
                ->label(trans('admin.fields.phone')),
            ExportColumn::make('total'),
            ExportColumn::make('status')
                ->getStateUsing(fn ($record) => $record->status->value),
            ExportColumn::make('currency'),
            ExportColumn::make('shipping.amount')
                ->label(trans('admin.order.shipping_cost')),
            ExportColumn::make('shipping.method')
                ->label(trans('admin.fields.shipping_method')),
            ExportColumn::make('notes')
                ->getStateUsing(fn ($record) => app(\App\Ecommerce\Order\Contracts\OrderServiceInterface::class)->getMetaValue($record, 'notes')),
            ExportColumn::make('type'),
        ];
    }
}
