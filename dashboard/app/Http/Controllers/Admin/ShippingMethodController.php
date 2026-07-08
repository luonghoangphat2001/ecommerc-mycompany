<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Model;

class ShippingMethodController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return ShippingMethod::class;
    }

    protected function title(): string
    {
        return 'admin.settings.manage_shipping_methods';
    }

    protected function routePrefix(): string
    {
        return 'admin.shipping-methods';
    }

    protected function searchable(): array
    {
        return ['name', 'type'];
    }

    protected function fields(): array
    {
        return [
            'shipping_zone_id' => [
                'label' => 'Shipping Zone',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:shop_shipping_zones,id'],
                'options' => ShippingZone::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'type' => [
                'label' => 'Type',
                'type' => 'select',
                'rules' => ['required', 'string', 'max:100'],
                'options' => [
                    'flat_rate' => 'Flat rate',
                    'free_shipping' => 'Free shipping',
                    'local_pickup' => 'Local pickup',
                ],
            ],
            'name' => ['label' => 'Tên method', 'rules' => ['required', 'string', 'max:255']],
            'settings_json' => ['label' => 'Settings JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'virtual' => true],
            'is_enabled' => ['label' => 'Kích hoạt', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $json = (string) ($data['settings_json'] ?? '');
        $data['settings'] = $json !== '' ? (json_decode($json, true) ?: []) : [];
        unset($data['settings_json']);

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'settings_json' => json_encode($record->settings ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}
