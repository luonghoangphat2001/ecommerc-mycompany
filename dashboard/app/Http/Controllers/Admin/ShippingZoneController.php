<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Shipping\Support\VietnamLocations;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasShippingTabs;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ShippingZoneController extends BaseCrudController
{
    use HasShippingTabs;

    protected function modelClass(): string
    {
        return ShippingZone::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.shipping_zones';
    }

    protected function routePrefix(): string
    {
        return 'admin.shipping-zones';
    }

    protected function activeTab(): ?string
    {
        return 'shipping-zones';
    }

    protected function formView(): string
    {
        return 'admin.shipping.zones.form';
    }

    protected function searchable(): array
    {
        return ['name', 'code'];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query->withCount('methods')->orderBy('sort');
    }

    protected function fields(): array
    {
        return [
            'code' => [
                'label' => 'Mã khu vực (Zone Code)',
                'rules' => ['nullable', 'string', 'max:50'],
            ],
            'name' => [
                'label' => 'Tên khu vực (Zone Name)',
                'rules' => ['required', 'string', 'max:255'],
                'translatable' => true,
            ],
            'formatted_locations' => [
                'label' => 'Phạm vi áp dụng (Locations)',
                'tableOnly' => true,
                'virtual' => true,
            ],
            'sort' => [
                'label' => 'Thứ tự ưu tiên (Sort Priority)',
                'type' => 'number',
                'rules' => ['nullable', 'integer', 'min:0'],
            ],
            'is_active' => [
                'label' => 'Trạng thái hoạt động',
                'type' => 'select',
                'options' => [
                    '1' => 'Đang hoạt động (Active)',
                    '0' => 'Tạm ngừng (Inactive)',
                ],
                'rules' => ['nullable', 'boolean'],
            ],
            'locations_json' => [
                'label' => 'Cấu hình địa phương (Locations)',
                'type' => 'hidden',
                'rules' => ['nullable'],
                'virtual' => true,
                'formOnly' => true,
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        if (isset($data['locations']) && is_array($data['locations'])) {
            // Already structured array
        } elseif (isset($data['locations_json'])) {
            $json = (string) $data['locations_json'];
            $data['locations'] = $json !== '' ? (json_decode($json, true) ?: []) : [];
            unset($data['locations_json']);
        } else {
            $data['locations'] = [];
        }

        if (isset($data['is_active'])) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        return [
            'northProvinces' => VietnamLocations::NORTH_PROVINCES,
            'centralProvinces' => VietnamLocations::CENTRAL_PROVINCES,
            'southProvinces' => VietnamLocations::SOUTH_PROVINCES,
            'continents' => config('continents', []),
            'countries' => config('countries', []),
            'initialLocations' => $record ? ($record->locations ?? []) : [['country' => 'VN', 'provinces' => []]],
            'sort' => $record ? $record->sort : 10,
            'is_active' => $record ? ($record->is_active ? '1' : '0') : '1',
        ];
    }
}
