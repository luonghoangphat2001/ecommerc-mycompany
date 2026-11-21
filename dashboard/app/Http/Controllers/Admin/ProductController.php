<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\Media\Services\MediaUrlService;
use App\Ecommerce\Product\Services\ProductAttributeService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Http\Controllers\Admin\Traits\HasHierarchicalOptions;
use App\Models\Brand;
use App\Models\ComboProduct;
use App\Models\ComboProductItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaxClass;
use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;

class ProductController extends BaseCrudController
{
    use HasAdminSelectOptions;
    use HasHierarchicalOptions;

    public function __construct(private readonly ProductAttributeService $attributeService)
    {
        parent::__construct();
    }

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

    protected function formView(): string
    {
        return 'admin.products.form';
    }

    protected function searchable(): array
    {
        return ['name', 'sku', 'slug', 'type'];
    }

    protected function filters(): array
    {
        return [
            'shop_brand_id' => [
                'label' => __('admin.fields.brand'),
                'placeholder' => __('admin.filters.all_brands'),
                'options' => $this->brandOptions('id'),
            ],
            'type' => [
                'label' => __('admin.fields.type'),
                'placeholder' => __('admin.filters.all_types'),
                'options' => [
                    'simple' => 'Simple Product',
                    'variable' => 'Variable Product',
                    'combo' => 'Combo Product',
                    'deliverable' => 'Deliverable (Simple)',
                ],
            ],
            'is_visible' => [
                'label' => __('admin.fields.is_visible'),
                'placeholder' => __('admin.filters.all_statuses'),
                'options' => $this->booleanOptions(),
            ],
            'featured' => [
                'label' => __('admin.fields.featured'),
                'placeholder' => __('admin.filters.all'),
                'options' => $this->booleanOptions(),
            ],
            'apply_tax' => [
                'label' => __('admin.fields.apply_tax'),
                'placeholder' => __('admin.filters.all'),
                'options' => $this->booleanOptions(),
            ],
            'category_id' => [
                'label' => __('admin.fields.category'),
                'placeholder' => __('admin.filters.all_categories'),
                'options' => $this->productCategoryOptions('id'),
            ],
        ];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        foreach (['shop_brand_id', 'type'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->query($field));
            }
        }

        foreach (['is_visible', 'featured', 'apply_tax'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->boolean($field));
            }
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn (Builder $categoryQuery) => $categoryQuery->whereKey($request->query('category_id')));
        }

        return $query;
    }

    protected function showGroups(): array
    {
        return [
            'general' => [
                'label' => __('admin.common.general_info'),
                'fields' => ['name', 'sku', 'slug', 'shop_brand_id', 'type', 'is_visible', 'featured', 'published_at', 'apply_tax', 'tax_class_id'],
            ],
            'pricing' => [
                'label' => __('admin.product.groups.pricing'),
                'fields' => ['price', 'old_price', 'cost', 'sale_price', 'sale_start_date', 'sale_end_date', 'qty', 'security_stock', 'backorder', 'requires_shipping'],
            ],
            'content' => [
                'label' => __('admin.fields.description'),
                'fields' => ['description'],
            ],
            'media' => [
                'label' => 'Media',
                'fields' => ['image'],
            ],
            'taxonomy' => [
                'label' => __('admin.post.groups.taxonomy'),
                'fields' => ['categories'],
            ],
            'seo' => [
                'label' => 'SEO Metadata',
                'fields' => ['seo_title', 'seo_description'],
            ],
            'loyalty' => [
                'label' => __('admin.sidebar.loyalty_settings'),
                'fields' => ['is_loyalty_earnable', 'is_loyalty_redeemable', 'loyalty_points_reward'],
            ],
        ];
    }

    protected function fields(): array
    {
        $yesNo = $this->booleanOptions();

        return [
            'image' => $this->imageField(__('admin.fields.image')),
            'name' => ['label' => __('admin.fields.name'), 'rules' => ['required', 'string']],
            'sku' => ['label' => 'SKU', 'rules' => ['required', 'string', 'max:100']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'hideOnIndex' => true],

            'shop_brand_id' => [
                'label' => __('admin.fields.brand'),
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:shop_brands,id'],
                'options' => ['' => '-- ' . __('admin.fields.brand') . ' --'] + $this->brandOptions('id'),
                'hideOnIndex' => true,
            ],

            'price' => ['label' => __('admin.fields.price'), 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0']],
            'old_price' => ['label' => __('admin.fields.old_price'), 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0'], 'hideOnIndex' => true],
            'cost' => ['label' => 'Cost', 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0'], 'hideOnIndex' => true],
            'sale_price' => ['label' => __('admin.fields.sale_price'), 'type' => 'number', 'rules' => ['nullable', 'numeric', 'min:0'], 'hideOnIndex' => true],
            'sale_start_date' => ['label' => __('admin.fields.sale_start_date'), 'type' => 'datetime-local', 'rules' => ['nullable', 'date'], 'hideOnIndex' => true],
            'sale_end_date' => ['label' => __('admin.fields.sale_end_date'), 'type' => 'datetime-local', 'rules' => ['nullable', 'date', 'after_or_equal:sale_start_date'], 'hideOnIndex' => true],
            'qty' => ['label' => __('admin.fields.qty'), 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],

            'security_stock' => ['label' => 'Security Stock', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0'], 'hideOnIndex' => true],

            'type' => [
                'label' => __('admin.fields.type'),
                'type' => 'select',
                'rules' => ['required', 'string', 'max:50'],
                'options' => [
                    'simple' => 'Simple Product',
                    'variable' => 'Variable Product',
                    'combo' => 'Combo Product',
                    'deliverable' => 'Deliverable',
                ],
                'hideOnIndex' => true,
            ],

            'tax_class_id' => [
                'label' => 'Thuế suất (Tax Class)',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:shop_tax_classes,id'],
                'options' => ['' => '-- Mặc định / Không thuế --'] + $this->taxClassOptions(),
                'hideOnIndex' => true,
            ],

            'featured' => ['label' => __('admin.fields.featured'), 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => $yesNo, 'hideOnIndex' => true],
            'is_visible' => ['label' => __('admin.fields.is_visible'), 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => $yesNo],
            'apply_tax' => ['label' => __('admin.fields.apply_tax'), 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => $yesNo],
            'backorder' => ['label' => 'Backorder', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => $yesNo, 'hideOnIndex' => true],
            'requires_shipping' => ['label' => 'Requires Shipping', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => $yesNo, 'hideOnIndex' => true],

            'published_at' => ['label' => __('admin.fields.published_at'), 'type' => 'date', 'rules' => ['nullable', 'date'], 'hideOnIndex' => true],
            'description' => ['label' => __('admin.fields.description'), 'type' => 'editor', 'rules' => ['nullable'], 'hideOnIndex' => true],

            'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255'], 'colspan' => 'full', 'hideOnIndex' => true],
            'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255'], 'hideOnIndex' => true],

            'categories' => [
                'label' => __('admin.fields.categories'),
                'type' => 'tree-select',
                'colspan' => 1,
                'rules' => ['nullable', 'array'],
                'tree_nodes' => $this->hierarchyTree(ProductCategory::class),
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

            'is_loyalty_earnable' => [
                'label' => __('admin.settings.field_labels.is_loyalty_earnable'),
                'type' => 'select',
                'rules' => ['nullable', 'boolean'],
                'options' => $yesNo,
                'hideOnIndex' => true,
            ],
            'is_loyalty_redeemable' => [
                'label' => __('admin.settings.field_labels.is_loyalty_redeemable'),
                'type' => 'select',
                'rules' => ['nullable', 'boolean'],
                'options' => $yesNo,
                'hideOnIndex' => true,
            ],
            'loyalty_points_reward' => [
                'label' => __('admin.settings.field_labels.loyalty_points_reward'),
                'type' => 'number',
                'rules' => ['nullable', 'integer', 'min:0'],
                'hideOnIndex' => true,
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);
        $brands = request()->input('brands');
        if (is_array($brands)) {
            $data['shop_brand_id'] = ! empty($brands) ? (int) $brands[0] : null;
        }
        unset($data['categories'], $data['tags'], $data['brands'], $data['attributes_json'], $data['variants_json'], $data['combo_items_json']);

        if (! isset($data['price']) || $data['price'] === '' || $data['price'] === null) {
            $data['price'] = 0;
        }

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        // 1. Sync Categories, Brands & Tags
        $record->categories()->sync((array) $request->input('categories', []));
        if ($request->has('brands')) {
            $brandIds = (array) $request->input('brands', []);
            $record->brands()->sync($brandIds);
            $primaryBrandId = ! empty($brandIds) ? (int) $brandIds[0] : null;
            if ($record->shop_brand_id !== $primaryBrandId) {
                $record->shop_brand_id = $primaryBrandId;
                $record->saveQuietly();
            }
        }
        $tagsInput = $request->input('tags', []);
        $tags = is_array($tagsInput) ? $tagsInput : collect(explode(',', (string) $tagsInput))->map(fn ($tag) => trim($tag))->filter()->values()->all();
        $record->syncTagsWithType($tags, 'Product');

        // 2. Variable Product handling
        if ($record->isVariable()) {
            $attributes = json_decode((string) $request->input('attributes_json', '[]'), true) ?: [];
            $variants = json_decode((string) $request->input('variants_json', '[]'), true) ?: [];

            $this->attributeService->syncProductAttributes($record, $attributes);
            $this->attributeService->syncProductVariants($record, $variants);
        }

        // 3. Combo Product handling
        if ($record->isCombo()) {
            $comboItems = json_decode((string) $request->input('combo_items_json', '[]'), true) ?: [];
            $record->comboComponents()->delete();

            $comboProduct = ComboProduct::updateOrCreate(
                ['product_id' => $record->id],
                [
                    'name' => is_array($record->name) ? ($record->name['vi'] ?? '') : (string) $record->name,
                    'slug' => $record->slug,
                    'description' => is_array($record->description) ? ($record->description['vi'] ?? '') : (string) $record->description,
                    'combo_price' => (int) $record->price,
                    'original_price' => (int) ($record->old_price ?? $record->price),
                    'is_active' => (bool) $record->is_visible,
                ]
            );

            $comboProduct->items()->delete();

            foreach ($comboItems as $idx => $item) {
                if (! empty($item['shop_product_id'])) {
                    ComboProductItem::create([
                        'combo_product_id' => $comboProduct->id,
                        'parent_product_id' => $record->id,
                        'shop_product_id' => (int) $item['shop_product_id'],
                        'product_variant_id' => ! empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null,
                        'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                        'sort_order' => $idx,
                    ]);
                }
            }

            $calculatedStock = $record->calculateComboStock();
            $record->updateQuietly([
                'qty' => $calculatedStock,
                'total_stock' => $calculatedStock,
            ]);
        }
    }

    protected function rules(?int $id = null): array
    {
        return [
            'name' => ['required'],
            'sku' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255'],
            'shop_brand_id' => ['nullable', 'integer', 'exists:shop_brands,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'old_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'sale_start_date' => ['nullable', 'date'],
            'sale_end_date' => ['nullable', 'date', 'after_or_equal:sale_start_date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'security_stock' => ['nullable', 'integer', 'min:0'],
            'type' => ['required'],
            'tax_class_id' => ['nullable', 'integer', 'exists:shop_tax_classes,id'],
            'featured' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'apply_tax' => ['nullable', 'boolean'],
            'backorder' => ['nullable', 'boolean'],
            'requires_shipping' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable'],
            'image' => ['nullable', 'image', 'max:5120'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:shop_categories,id'],
            'brands' => ['nullable', 'array'],
            'brands.*' => ['integer', 'exists:shop_brands,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'attributes_json' => ['nullable', 'string'],
            'variants_json' => ['nullable', 'string'],
            'combo_items_json' => ['nullable', 'string'],
        ];
    }

    protected function formData(?Model $record = null): array
    {
        $oldInput = session()->getOldInput();
        $availableAttributes = $this->attributeService->getGlobalAttributes(true)->map(function ($attr) {
            return [
                'id' => $attr->id,
                'name' => $attr->name,
                'slug' => $attr->slug,
                'type' => $attr->type,
                'values' => is_array($attr->values) ? array_values($attr->values) : [],
            ];
        })->values()->toArray();

        $categoryTree = $this->hierarchyTree(ProductCategory::class);
        $brandTree = $this->hierarchyTree(Brand::class);

        if (! $record) {
            $productType = $oldInput['type'] ?? 'simple';

            return [
                'type' => in_array($productType, ['simple', 'variable'], true) ? $productType : 'simple',
                'attributes_json' => '[]',
                'variants_json' => '[]',
                'combo_items_json' => '[]',
                'categories' => $oldInput['categories'] ?? [],
                'brands' => $oldInput['brands'] ?? [],
                'tags' => $oldInput['tags'] ?? [],
                'image' => $oldInput['image'] ?? null,
                'imageUrl' => app(MediaUrlService::class)->url($oldInput['image'] ?? null),
                'taxClasses' => TaxClass::with('rates')->get(),
                'allTags' => Tag::getWithType('Product'),
                'availableAttributes' => $availableAttributes,
                'categoryTree' => $categoryTree,
                'brandTree' => $brandTree,
            ];
        }

        $imagePath = $oldInput['image'] ?? Media::path($record->product_images, $record->featuredImage, $record->image);
        $imageUrl = app(MediaUrlService::class)->url($imagePath);

        $attributes = $record->productAttributes()->get(['id', 'attribute_id', 'name', 'options', 'is_variation'])->toArray();
        $variants = $record->variants()->get(['id', 'sku', 'price', 'sale_price', 'sale_start_date', 'sale_end_date', 'qty', 'attributes', 'is_active'])->map(function ($v) {
            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'price' => $v->price,
                'sale_price' => $v->sale_price,
                'sale_start_date' => $v->sale_start_date ? $v->sale_start_date->format('Y-m-d\TH:i') : '',
                'sale_end_date' => $v->sale_end_date ? $v->sale_end_date->format('Y-m-d\TH:i') : '',
                'qty' => $v->qty,
                'attributes' => $v->attributes,
                'is_active' => (bool) $v->is_active,
            ];
        })->toArray();
        $comboItems = $record->comboComponents()->get(['shop_product_id', 'product_variant_id', 'quantity'])->toArray();

        $selectedBrands = $oldInput['brands'] ?? $record->brands()->pluck('shop_brands.id')->toArray();
        if (empty($selectedBrands) && $record->shop_brand_id) {
            $selectedBrands = [$record->shop_brand_id];
        }

        return [
            'type' => in_array($oldInput['type'] ?? $record->type?->value, ['simple', 'variable'], true)
                ? ($oldInput['type'] ?? $record->type?->value)
                : 'simple',
            'attributes_json' => json_encode($attributes, JSON_UNESCAPED_UNICODE),
            'variants_json' => json_encode($variants, JSON_UNESCAPED_UNICODE),
            'combo_items_json' => json_encode($comboItems, JSON_UNESCAPED_UNICODE),
            'categories' => $oldInput['categories'] ?? $record->categories()->pluck('shop_categories.id')->toArray(),
            'brands' => $selectedBrands,
            'tags' => $oldInput['tags'] ?? $record->tags->pluck('name')->toArray(),
            'image' => $imagePath,
            'imageUrl' => $imageUrl,
            'taxClasses' => TaxClass::with('rates')->get(),
            'allTags' => Tag::getWithType('Product'),
            'availableAttributes' => $availableAttributes,
            'categoryTree' => $categoryTree,
            'brandTree' => $brandTree,
        ];
    }
}
