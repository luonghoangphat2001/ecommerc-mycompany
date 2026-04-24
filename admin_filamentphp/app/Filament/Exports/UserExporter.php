<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;

class UserExporter extends BaseExporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ...static::commonColumns(),
            ExportColumn::make('name')
                ->label(trans('admin.fields.name')),
            ExportColumn::make('email')
                ->label(trans('admin.fields.email')),
        ];
    }
}
