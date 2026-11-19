<?php

namespace App\Http\Controllers\Admin\Traits;

use App\Models\Brand;
use App\Models\Department;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShippingZone;
use App\Models\TaxClass;
use App\Models\User;

trait HasAdminSelectOptions
{
    private array $adminSelectOptionsCache = [];

    protected function userOptions(): array
    {
        return $this->adminSelectOptionsCache['users'] ??= User::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    protected function departmentOptions(): array
    {
        return $this->adminSelectOptionsCache['departments'] ??= Department::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    protected function brandOptions(string $orderBy = 'name'): array
    {
        $key = 'brands:' . $orderBy;

        return $this->adminSelectOptionsCache[$key] ??= Brand::query()->orderBy($orderBy)->pluck('name', 'id')->toArray();
    }

    protected function productCategoryOptions(string $orderBy = 'name'): array
    {
        $key = 'categories:' . $orderBy;

        return $this->adminSelectOptionsCache[$key] ??= ProductCategory::query()->orderBy($orderBy)->pluck('name', 'id')->toArray();
    }

    protected function pageRecords()
    {
        return $this->adminSelectOptionsCache['page_records'] ??= Page::query()
            ->select('id', 'title', 'slug')
            ->orderBy('id')
            ->get();
    }

    protected function productCategoryRecords()
    {
        return $this->adminSelectOptionsCache['product_category_records'] ??= ProductCategory::query()
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();
    }

    protected function taxClassOptions(): array
    {
        return $this->adminSelectOptionsCache['tax_classes'] ??= TaxClass::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    protected function productOptions(bool $visibleOnly = false): array
    {
        $key = $visibleOnly ? 'visible_products' : 'products';
        if (isset($this->adminSelectOptionsCache[$key])) {
            return $this->adminSelectOptionsCache[$key];
        }

        $query = Product::query()->orderBy('name');

        if ($visibleOnly) {
            $query->where('is_visible', true);
        }

        return $this->adminSelectOptionsCache[$key] = $query->pluck('name', 'id')->toArray();
    }

    protected function productRecords()
    {
        return $this->adminSelectOptionsCache['product_records'] ??= Product::query()
            ->select('id', 'name', 'sku')
            ->orderBy('name')
            ->get();
    }

    protected function shippingZoneOptions(): array
    {
        return $this->adminSelectOptionsCache['shipping_zones'] ??= ShippingZone::query()->orderBy('sort')->pluck('name', 'id')->toArray();
    }

    protected function shippingZoneRecords()
    {
        return $this->adminSelectOptionsCache['shipping_zone_records'] ??= ShippingZone::query()->orderBy('sort')->get();
    }

    protected function sortOrderField(): array
    {
        return [
            'label' => 'Sort order',
            'type' => 'number',
            'rules' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function booleanOptions(): array
    {
        return ['1' => __('admin.common.yes'), '0' => __('admin.common.no')];
    }

    protected function imageField(string $label, int $maxKilobytes = 5120): array
    {
        return [
            'label' => $label,
            'type' => 'image',
            'rules' => ['nullable', 'image', 'max:' . $maxKilobytes],
        ];
    }

    protected function activeField(): array
    {
        return [
            'label' => 'Kích hoạt',
            'type' => 'select',
            'rules' => ['nullable', 'boolean'],
            'options' => ['1' => 'Có', '0' => 'Không'],
        ];
    }
}
