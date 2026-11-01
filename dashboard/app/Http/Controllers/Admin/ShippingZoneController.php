<?php

namespace App\Http\Controllers\Admin;

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
                'label' => 'Cấu hình địa phương (Locations JSON)',
                'type' => 'textarea',
                'rules' => ['nullable', 'string'],
                'virtual' => true,
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        $json = (string) ($data['locations_json'] ?? '');
        $data['locations'] = $json !== '' ? (json_decode($json, true) ?: []) : [];
        unset($data['locations_json']);

        if (isset($data['is_active'])) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        if (!$record) {
            return [
                'sort' => 10,
                'is_active' => '1',
                'locations_json' => json_encode([
                    [
                        'country' => 'VN',
                        'provinces' => [],
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ];
        }

        return [
            'is_active' => $record->is_active ? '1' : '0',
            'locations_json' => json_encode($record->locations ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}
