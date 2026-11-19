<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasHierarchicalOptions;
use App\Http\Controllers\Admin\Traits\HasVisibilityMetrics;
use App\Http\Controllers\Admin\Traits\HasVisibilityToggle;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends BaseCrudController
{
    use HasHierarchicalOptions;
    use HasVisibilityMetrics;
    use HasVisibilityToggle;

    protected function modelClass(): string
    {
        return Brand::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.brands';
    }

    protected function routePrefix(): string
    {
        return 'admin.brands';
    }

    protected function searchable(): array
    {
        return ['name', 'slug', 'website'];
    }

    protected function hierarchyRootLabel(): string
    {
        return 'Thương hiệu gốc';
    }

    protected function visibilityEntityLabel(): string
    {
        return 'thương hiệu';
    }

    protected function fields(): array
    {
        return [
            'name' => [
                'label' => __('admin.fields.name'),
                'rules' => ['required'],
            ],
            'slug' => [
                'label' => 'Slug (Đường dẫn tĩnh)',
                'rules' => ['required', 'string', 'max:255'],
            ],
            'parent_id' => [
                'label' => __('admin.fields.parent'),
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:shop_brands,id'],
                'options' => $this->getParentCategoryOptions(request()->route('brand')?->id ?? request()->route('brand')),
            ],
            'website' => [
                'label' => 'Website chính thức',
                'rules' => ['nullable', 'url'],
            ],
            'description' => [
                'label' => __('admin.fields.description'),
                'type' => 'textarea',
                'rules' => ['nullable'],
            ],
            'sort' => [
                'label' => __('admin.fields.sort'),
                'type' => 'number',
                'rules' => ['nullable', 'integer', 'min:0'],
            ],
            'is_visible' => [
                'label' => __('admin.fields.is_visible'),
                'type' => 'select',
                'rules' => ['nullable', 'boolean'],
                'options' => ['1' => __('admin.common.yes') . ' (Hiển thị)', '0' => __('admin.common.no') . ' (Tạm ẩn)'],
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        return $data;
    }

    public function index(Request $request): View
    {
        $totalProductsCount = Product::whereNotNull('shop_brand_id')->count();

        $metrics = $this->visibilityMetrics([
            'total_items' => $totalProductsCount,
        ]);

        $allBrands = Brand::withCount('directProducts')
            ->orderBy('order_column')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $hierarchy = $this->hierarchyCollections($allBrands);

        return view('admin.crud.tree', [
            'title' => __($this->title()),
            'allCategories' => $allBrands,
            'groupedCategories' => $hierarchy['grouped'],
            'rootCategories' => $hierarchy['roots'],
            'metrics' => $metrics,
            'routePrefix' => $this->routePrefix(),
            'entityLabel' => 'thương hiệu',
            'entityPlural' => 'thương hiệu',
            'itemCountRelation' => 'direct_products_count',
            'itemCountLabel' => 'sp',
            'itemCountRoute' => 'admin.products.index',
            'itemCountParam' => 'shop_brand_id',
            'itemCountIcon' => 'package',
            'createLabel' => __('admin.actions.create'),
        ]);
    }

    protected function formData(?Model $record = null): array
    {
        if ($record) {
            return [];
        }

        return [
            'parent_id' => request()->integer('parent_id') ?: null,
        ];
    }
}
