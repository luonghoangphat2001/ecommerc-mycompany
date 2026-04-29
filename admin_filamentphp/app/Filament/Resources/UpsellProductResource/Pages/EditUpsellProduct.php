<?php

namespace App\Filament\Resources\UpsellProductResource\Pages;

use App\Filament\Resources\UpsellProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUpsellProduct extends EditRecord
{
    protected static string $resource = UpsellProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
