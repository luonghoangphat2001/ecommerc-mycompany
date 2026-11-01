<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasShippingTabs;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ShippingMethodController extends BaseCrudController
{
    use HasShippingTabs;

    protected function modelClass(): string
    {
        return ShippingMethod::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.shipping_methods';
    }

    protected function routePrefix(): string
    {
        return 'admin.shipping-methods';
    }

    protected function activeTab(): ?string
    {
        return 'shipping-methods';
    }

    protected function formView(): string
    {
        return 'admin.shipping.methods.form';
    }

    protected function searchable(): array
    {
        return ['name', 'type'];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        if ($zoneId = $request->input('shipping_zone_id')) {
            $query->where('shipping_zone_id', $zoneId);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        return $query->with('zone');
    }

    protected function filters(): array
    {
        return [
            'shipping_zone_id' => [
                'label' => 'Khu vực (Shipping Zone)',
                'type' => 'select',
                'options' => ShippingZone::orderBy('sort')->get()->mapWithKeys(fn ($z) => [$z->id => $z->name])->toArray(),
            ],
            'type' => [
                'label' => 'Loại phương thức',
                'type' => 'select',
                'options' => [
                    'flat_rate' => 'Đồng giá (Flat rate)',
                    'free_shipping' => 'Miễn phí (Free shipping)',
                    'weight_based' => 'Theo khối lượng (Weight based)',
                    'carrier' => 'API Hãng vận chuyển (Carrier)',
                    'local_pickup' => 'Nhận tại cửa hàng (Local pickup)',
                ],
            ],
        ];
    }

    protected function fields(): array
    {
        return [
            'shipping_zone_id' => [
                'label' => 'Khu vực vận chuyển (Shipping Zone)',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:shop_shipping_zones,id'],
                'options' => ShippingZone::orderBy('sort')->get()->mapWithKeys(fn ($z) => [$z->id => $z->name])->toArray(),
            ],
            'type' => [
                'label' => 'Loại phương thức',
                'type' => 'select',
                'rules' => ['required', 'string', 'max:100'],
                'options' => [
                    'flat_rate' => 'Đồng giá (Flat rate)',
                    'free_shipping' => 'Miễn phí vận chuyển (Free shipping)',
                    'weight_based' => 'Theo khối lượng (Weight based)',
                    'carrier' => 'API Hãng vận chuyển (Carrier API)',
                    'local_pickup' => 'Nhận tại cửa hàng (Local pickup)',
                ],
            ],
            'name' => [
                'label' => 'Tên phương thức hiển thị',
                'rules' => ['required', 'string', 'max:255'],
                'translatable' => true,
            ],
            'settings_json' => [
                'label' => 'Cấu hình chi tiết',
                'type' => 'hidden',
                'rules' => ['nullable'],
                'virtual' => true,
                'formOnly' => true,
            ],
            'is_enabled' => [
                'label' => 'Kích hoạt',
                'type' => 'select',
                'rules' => ['nullable', 'boolean'],
                'options' => ['1' => 'Bật (Enabled)', '0' => 'Tắt (Disabled)'],
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        if (isset($data['settings']) && is_array($data['settings'])) {
            // Already structured array
        } elseif (isset($data['settings_json'])) {
            $json = (string) $data['settings_json'];
            $data['settings'] = $json !== '' ? (json_decode($json, true) ?: []) : [];
            unset($data['settings_json']);
        } else {
            $data['settings'] = [];
        }

        if (isset($data['is_enabled'])) {
            $data['is_enabled'] = (bool) $data['is_enabled'];
        }

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        $defaultSettings = [
            'cost' => 30000,
            'currency' => 'VND',
            'estimated_days' => 2,
            'free_shipping_threshold' => 500000,
            'description' => 'Giao hàng tiêu chuẩn 1-2 ngày làm việc.',
            'min_amount' => 500000,
            'additional_cost_per_kg' => 5000,
            'rules' => [
                ['min_weight' => 0, 'max_weight' => 1000, 'cost' => 25000],
                ['min_weight' => 1001, 'max_weight' => 2000, 'cost' => 35000],
            ],
            'carrier' => 'ghn',
            'service_id' => 'standard',
            'markup' => 0,
            'fallback_cost' => 30000,
            'pickup_address' => '',
        ];

        $currentSettings = $record ? ($record->settings ?? []) : [];
        if (isset($currentSettings['description']) && is_array($currentSettings['description'])) {
            $currentSettings['description'] = $currentSettings['description']['vi'] ?? reset($currentSettings['description']);
        }

        $mergedSettings = array_merge($defaultSettings, $currentSettings);

        return [
            'zones' => ShippingZone::orderBy('sort')->get(),
            'initialSettings' => $mergedSettings,
            'currentType' => $record ? ($record->type instanceof \BackedEnum ? $record->type->value : (string) $record->type) : 'flat_rate',
            'is_enabled' => $record ? ($record->is_enabled ? '1' : '0') : '1',
            'settings_json' => json_encode($mergedSettings, JSON_UNESCAPED_UNICODE),
        ];
    }
}
