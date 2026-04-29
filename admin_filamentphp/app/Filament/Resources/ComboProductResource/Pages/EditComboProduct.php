<?php

namespace App\Filament\Resources\ComboProductResource\Pages;

use App\Filament\Resources\ComboProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComboProduct extends EditRecord
{
    protected static string $resource = ComboProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
