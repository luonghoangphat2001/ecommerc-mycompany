<?php

namespace App\Filament\Resources\CrossSellProductResource\Pages;

use App\Filament\Resources\CrossSellProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCrossSellProduct extends EditRecord
{
    protected static string $resource = CrossSellProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
