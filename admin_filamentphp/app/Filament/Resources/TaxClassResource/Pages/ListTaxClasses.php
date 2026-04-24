<?php

namespace App\Filament\Resources\TaxClassResource\Pages;

use App\Filament\Resources\TaxClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxClasses extends ListRecords
{
    protected static string $resource = TaxClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
