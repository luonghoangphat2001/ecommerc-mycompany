<?php

namespace App\Filament\Imports;

use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

abstract class BaseImporter extends Importer
{
    public static function getCompletedNotificationBody(Import $import): string
    {
        $rows  = number_format($import->successful_rows);
        $label = str('row')->plural($import->successful_rows);
        $body  = trans('admin.import.completed', ['rows' => $rows]);

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' ' . trans('admin.import.failed', ['rows' => number_format($failed)]);
        }

        return $body;
    }
}
