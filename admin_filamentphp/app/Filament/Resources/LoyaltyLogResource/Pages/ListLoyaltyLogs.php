<?php

namespace App\Filament\Resources\LoyaltyLogResource\Pages;

use App\Filament\Resources\LoyaltyLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyLogs extends ListRecords
{
    protected static string $resource = LoyaltyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
