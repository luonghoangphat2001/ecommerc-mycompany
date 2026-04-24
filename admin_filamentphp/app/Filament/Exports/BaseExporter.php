<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

abstract class BaseExporter extends Exporter
{
    protected static function commonColumns(): array
    {
        return [
            ExportColumn::make('id')->label(trans('admin.fields.id')),
            ExportColumn::make('created_at')->label(trans('admin.fields.created_at')),
            ExportColumn::make('updated_at')->label(trans('admin.fields.updated_at')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $rows  = number_format($export->successful_rows);
        $label = str('row')->plural($export->successful_rows);
        $body  = trans('admin.export.completed', ['rows' => $rows]);

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' ' . trans('admin.export.failed', ['rows' => number_format($failed)]);
        }

        return $body;
    }
}
