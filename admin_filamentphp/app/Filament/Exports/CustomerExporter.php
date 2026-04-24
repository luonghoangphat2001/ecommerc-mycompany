<?php

namespace App\Filament\Exports;

use App\Filament\Exports\BaseExporter;
use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;

class CustomerExporter extends BaseExporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ...static::commonColumns(),
            ExportColumn::make('name')
                ->label(trans('admin.fields.name')),
            ExportColumn::make('email')
                ->label(trans('admin.fields.email')),
            ExportColumn::make('gender')
                ->label(trans('admin.fields.gender')),
            ExportColumn::make('phone')
                ->label(trans('admin.fields.phone')),
            ExportColumn::make('birthday')
                ->label(trans('admin.fields.birthday')),
        ];
    }
}
