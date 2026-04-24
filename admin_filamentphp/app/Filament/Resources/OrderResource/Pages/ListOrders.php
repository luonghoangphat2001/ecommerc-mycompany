<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Exports\OrderExporter;
use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()->exporter(OrderExporter::class),
            Actions\CreateAction::make()->label(trans('admin.create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return static::getResource()::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            trans('admin.new') => Tab::make()->query(fn($query) => $query->status('new')),
            trans('admin.processing') => Tab::make()->query(fn($query) => $query->status('processing')),
            trans('admin.delivered') => Tab::make()->query(fn($query) => $query->status('delivered')),
            trans('admin.cancelled') => Tab::make()->query(fn($query) => $query->status('cancelled')),
        ];
    }
}
