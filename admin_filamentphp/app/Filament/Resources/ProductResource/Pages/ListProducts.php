<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    use ExposesTableToWidgets;
    use ListRecords\Concerns\Translatable;

    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return static::getResource()::getWidgets();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()->exporter(ProductExporter::class),
            Actions\ImportAction::make()->importer(ProductImporter::class),
            Actions\CreateAction::make()->label(trans('admin.create')),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
