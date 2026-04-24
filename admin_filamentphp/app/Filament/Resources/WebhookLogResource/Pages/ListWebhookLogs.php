<?php

namespace App\Filament\Resources\WebhookLogResource\Pages;

use App\Filament\Resources\WebhookLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebhookLogs extends ListRecords
{
    protected static string $resource = WebhookLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => \Filament\Resources\Components\Tab::make('All'),
            'delivered' => \Filament\Resources\Components\Tab::make('Delivered')
                ->query(fn ($query) => $query->status('delivered')),
            'failed' => \Filament\Resources\Components\Tab::make('Failed')
                ->query(fn ($query) => $query->status('failed')),
        ];
    }
}
