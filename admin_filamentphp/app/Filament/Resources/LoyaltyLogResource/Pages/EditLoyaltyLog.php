<?php

namespace App\Filament\Resources\LoyaltyLogResource\Pages;

use App\Filament\Resources\LoyaltyLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyLog extends EditRecord
{
    protected static string $resource = LoyaltyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
