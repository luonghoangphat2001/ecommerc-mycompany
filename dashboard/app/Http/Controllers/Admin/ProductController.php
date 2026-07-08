<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProductController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Product::class;
    }

    protected function title(): string
    {
        return 'Products';
    }

    protected function routePrefix(): string
    {
        return 'admin.products';
    }

    protected function searchable(): array
    {
        return ['sku', 'slug', 'type'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Tên', 'rules' => ['required', 'string']],
            'sku' => ['label' => 'SKU', 'rules' => ['required', 'string', 'max:100']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'shop_brand_id' => [
                'label' => 'Brand',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:shop_brands,id'],
                'options' => ['' => '-- Chọn brand --'] + Brand::orderBy('id')->pluck('slug', 'id')->toArray(),
            ],
            'price' => ['label' => 'Giá', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
            'old_price' => ['label' => 'Giá gốc', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0']],
            'cost' => ['label' => 'Cost', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0']],
            'qty' => ['label' => 'Qty', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'security_stock' => ['label' => 'Security Stock', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'type' => ['label' => 'Type', 'type' => 'select', 'rules' => ['required', 'string', 'max:50'], 'options' => ['deliverable' => 'deliverable', 'downloadable' => 'downloadable', 'service' => 'service']],
            'featured' => ['label' => 'Featured', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'backorder' => ['label' => 'Backorder', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'requires_shipping' => ['label' => 'Requires Shipping', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'published_at' => ['label' => 'Published At', 'type' => 'date', 'rules' => ['nullable', 'date']],
            'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255']],
            'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255']],
            'categories' => [
                'label' => 'Categories',
                'type' => 'multiselect',
                'rules' => ['nullable', 'array'],
                'options' => ProductCategory::orderBy('id')->pluck('slug', 'id')->toArray(),
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        unset($data['categories']);

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $record->categories()->sync((array) $request->input('categories', []));
    }

    protected function rules(?int $id = null): array
    {
        return [
            'name' => ['required'],
            'sku' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255'],
            'shop_brand_id' => ['nullable', 'integer', 'exists:shop_brands,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'security_stock' => ['nullable', 'integer', 'min:0'],
            'type' => ['required', 'string', 'max:50'],
            'featured' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'backorder' => ['nullable', 'boolean'],
            'requires_shipping' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:shop_categories,id'],
        ];
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'categories' => $record->categories()->pluck('shop_categories.id')->toArray(),
        ];
    }
}
