<?php



namespace App\Filament\Resources\ProductCategoryResource\Pages;

use App\Filament\Resources\ProductCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Concerns\Translatable;
use Filament\Actions;

class CreateCategory extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    protected static string $resource = ProductCategoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
