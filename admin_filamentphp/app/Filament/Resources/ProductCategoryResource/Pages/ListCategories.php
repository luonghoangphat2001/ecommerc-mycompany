<?php

namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Exports\ProductCategoryExporter;
use App\Filament\Imports\ProductCategoryImporter;
use App\Filament\Resources\ProductCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()->exporter(ProductCategoryExporter::class),
            Actions\ImportAction::make()->importer(ProductCategoryImporter::class),
            Actions\CreateAction::make()->label(trans('admin.create')),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
