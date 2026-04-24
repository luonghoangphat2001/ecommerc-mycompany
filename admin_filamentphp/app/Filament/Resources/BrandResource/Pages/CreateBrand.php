<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateBrand extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    protected static string $resource = BrandResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
