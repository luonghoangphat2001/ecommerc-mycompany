<?php

namespace App\Filament\Resources\TaxClassResource\Pages;

use App\Filament\Resources\TaxClassResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTaxClass extends ViewRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
