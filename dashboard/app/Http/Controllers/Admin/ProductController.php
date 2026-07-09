<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;

class ProductController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Product::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.products';
    }

    protected function routePrefix(): string
    {
        return 'admin.products';
    }

    protected function searchable(): array
    {
        return ['sku', 'slug', 'type'];
    }

    protected function showGroups(): array
    {
        return [
            'general' => [
                'label' => 'Thông tin chung',
                'fields' => ['name', 'sku', 'slug', 'shop_brand_id', 'type', 'is_visible', 'featured', 'published_at'],
            ],
            'pricing' => [
                'label' => 'Giá & Tồn kho',
                'fields' => ['price', 'old_price', 'cost', 'qty', 'security_stock', 'backorder', 'requires_shipping'],
            ],
            'content' => [
                'label' => 'Mô tả',
                'fields' => ['description'],
            ],
            'media' => [
                'label' => 'Media',
                'fields' => ['image'],
            ],
            'taxonomy' => [
                'label' => 'Phân loại',
                'fields' => ['categories'],
            ],
            'seo' => [
                'label' => 'SEO Metadata',
                'fields' => ['seo_title', 'seo_description'],
            ],
        ];
    }

    protected function fields(): array
    {
        return [
            'image' => ['label' => 'Image', 'type' => 'image', 'rules' => ['nullable', 'image', 'max:5120']],
            'name' => ['label' => 'Tên', 'rules' => ['required', 'string']],
            'sku' => ['label' => 'SKU', 'rules' => ['required', 'string', 'max:100']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'hideOnIndex' => true],

            'shop_brand_id' => [
                'label' => 'Brand',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:shop_brands,id'],
                'options' => ['' => '-- Chọn brand --'] + Brand::orderBy('id')->pluck('slug', 'id')->toArray(),
                'hideOnIndex' => true,
            ],

            'price' => ['label' => 'Giá', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
            'old_price' => ['label' => 'Giá gốc', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0'], 'hideOnIndex' => true],
            'cost' => ['label' => 'Cost', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0'], 'hideOnIndex' => true],
            'qty' => ['label' => 'Qty', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],

            'security_stock' => ['label' => 'Security Stock', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0'], 'hideOnIndex' => true],

            'type' => [
                'label' => 'Type',
                'type' => 'select',
                'rules' => ['required', 'string', 'max:50'],
                'options' => [
                    'deliverable' => 'deliverable',
                    'downloadable' => 'downloadable',
                    'service' => 'service',
                ],
                'hideOnIndex' => true,
            ],

            'featured' => ['label' => 'Featured', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không'], 'hideOnIndex' => true],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'backorder' => ['label' => 'Backorder', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không'], 'hideOnIndex' => true],
            'requires_shipping' => ['label' => 'Requires Shipping', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không'], 'hideOnIndex' => true],

            'published_at' => ['label' => 'Published At', 'type' => 'date', 'rules' => ['nullable', 'date'], 'hideOnIndex' => true],
            'description' => ['label' => 'Mô tả sản phẩm', 'type' => 'editor', 'rules' => ['nullable', 'string'], 'hideOnIndex' => true],

            'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255'], 'colspan' => 'full', 'hideOnIndex' => true],
            'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255'], 'hideOnIndex' => true],

            'categories' => [
                'label' => 'Categories',
                'type' => 'tree-select',
                'colspan' => 1,
                'rules' => ['nullable', 'array'],
                'tree_nodes' => ProductCategory::whereNull('parent_id')->with('children.children')->get(),
                'hideOnIndex' => true,
            ],

            'tags' => [
                'label' => 'Tags',
                'type' => 'tags-checkboxes',
                'colspan' => 1,
                'rules' => ['nullable', 'array'],
                'options' => Tag::withType('Product')->pluck('name', 'name')->toArray(),
                'hideOnIndex' => true,
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);
        unset($data['categories'], $data['tags']);
        if (isset($data['image'])) {
            $data['product_images'] = [$data['image']];
            unset($data['image']);
        }
        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $record->categories()->sync((array) $request->input('categories', []));
        $tagsInput = $request->input('tags', []);
        $tags = is_array($tagsInput) ? $tagsInput : collect(explode(',', (string) $tagsInput))->map(fn($tag) => trim($tag))->filter()->values()->all();
        $record->syncTagsWithType($tags, 'Product');
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
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:shop_categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ];
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        $imagePath = null;
        if ($record->product_images) {
            $imageId = is_array($record->product_images) ? ($record->product_images[0] ?? null) : $record->product_images;
            if (is_numeric($imageId) && $record->featuredImage) {
                $imagePath = $record->featuredImage->path;
            } else {
                $imagePath = $imageId;
            }
        }

        return [
            'categories' => $record->categories()->pluck('shop_categories.id')->toArray(),
            'tags' => $record->tags->pluck('name')->toArray(),
            'image' => $imagePath,
        ];
    }
}
