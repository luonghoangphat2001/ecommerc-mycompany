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
                'label' => 'Cấu hình chi tiết (Settings JSON)',
                'type' => 'textarea',
                'rules' => ['nullable', 'string'],
                'virtual' => true,
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

        $json = (string) ($data['settings_json'] ?? '');
        $data['settings'] = $json !== '' ? (json_decode($json, true) ?: []) : [];
        unset($data['settings_json']);

        if (isset($data['is_enabled'])) {
            $data['is_enabled'] = (bool) $data['is_enabled'];
        }

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        if (!$record) {
            return [
                'is_enabled' => '1',
                'settings_json' => json_encode([
                    'cost' => 30000,
                    'currency' => 'VND',
                    'estimated_days' => 2,
                    'free_shipping_threshold' => 500000,
                    'description' => 'Giao hàng tiêu chuẩn 1-2 ngày làm việc.',
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ];
        }

        return [
            'is_enabled' => $record->is_enabled ? '1' : '0',
            'settings_json' => json_encode($record->settings ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}
