<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Model;

class ShippingZoneController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return ShippingZone::class;
    }

    protected function title(): string
    {
        return 'admin.settings.manage_shipping_zones';
    }

    protected function routePrefix(): string
    {
        return 'admin.shipping-zones';
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Tên zone', 'rules' => ['required', 'string', 'max:255']],
            'sort' => ['label' => 'Sort', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'locations_json' => ['label' => 'Locations JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'virtual' => true],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $json = (string) ($data['locations_json'] ?? '');
        $data['locations'] = $json !== '' ? (json_decode($json, true) ?: []) : [];
        unset($data['locations_json']);

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'locations_json' => json_encode($record->locations ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}
