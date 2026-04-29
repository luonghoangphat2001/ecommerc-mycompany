<?php

namespace App\Filament\Resources\CrossSellProductResource\Pages;

use App\Filament\Resources\CrossSellProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCrossSellProducts extends ListRecords
{
    protected static string $resource = CrossSellProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
