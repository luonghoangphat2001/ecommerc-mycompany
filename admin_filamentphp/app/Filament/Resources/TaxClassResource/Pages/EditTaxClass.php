<?php

namespace App\Filament\Resources\TaxClassResource\Pages;

use App\Filament\Resources\TaxClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaxClass extends EditRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
