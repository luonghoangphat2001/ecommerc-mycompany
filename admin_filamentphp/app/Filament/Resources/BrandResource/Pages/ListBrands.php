<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Exports\BrandExporter;
use App\Filament\Imports\BrandImporter;
use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBrands extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()->exporter(BrandExporter::class),
            Actions\ImportAction::make()->importer(BrandImporter::class),
            Actions\CreateAction::make()->label(trans('admin.create')),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
