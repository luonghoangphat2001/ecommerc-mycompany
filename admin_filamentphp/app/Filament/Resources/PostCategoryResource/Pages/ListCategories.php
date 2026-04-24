<?php

namespace App\Filament\Resources\PostCategoryResource\Pages;

use App\Filament\Exports\PostCategoryExporter;
use App\Filament\Imports\PostCategoryImporter;
use App\Filament\Resources\PostCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = PostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()->exporter(PostCategoryExporter::class),
            Actions\ImportAction::make()->importer(PostCategoryImporter::class),
            Actions\CreateAction::make()->label(trans('admin.create')),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
