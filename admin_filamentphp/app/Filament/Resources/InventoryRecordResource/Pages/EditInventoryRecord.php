<?php

namespace App\Filament\Resources\InventoryRecordResource\Pages;

use App\Filament\Resources\InventoryRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventoryRecord extends EditRecord
{
    protected static string $resource = InventoryRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('process')
                ->label(trans('admin.process'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record && $record->status === 'DRAFT')

                ->action(function () {
                    app(\App\Ecommerce\Inventory\Services\InventoryService::class)->processRecord($this->record->id);
                    $this->refreshFormData(['status']);
                }),
            Actions\DeleteAction::make(),
        ];

    }
}
