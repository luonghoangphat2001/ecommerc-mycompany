<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends BaseCrudController
{
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

    public function getParentCategoryOptions(?int $excludeId = null): array
    {
        $brands = Brand::orderBy('order_column')->orderBy('sort')->orderBy('id')->get();
        $options = ['' => '-- ' . __('admin.common.none') . ' (Thương hiệu gốc) --'];

        $buildTree = function ($parentId, $prefix = '') use (&$buildTree, &$options, $brands, $excludeId) {
            foreach ($brands->where('parent_id', $parentId) as $brand) {
                if ($excludeId && $brand->id == $excludeId) {
                    continue;
                }
                $name = $brand->getTranslation('name', 'vi') ?: $brand->slug;
                $options[$brand->id] = $prefix ? ($prefix . '↳ ' . $name) : $name;
                $buildTree($brand->id, $prefix . '    ');
            }
        };

        $buildTree(null);

        return $options;
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

    protected function mutateData(array $data, ?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        $data = parent::mutateData($data, $record);

        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        return $data;
    }

    public function index(Request $request): View
    {
        $totalCount = Brand::count();
        $visibleCount = Brand::where('is_visible', true)->count();
        $hiddenCount = Brand::where('is_visible', false)->count();
        $rootCount = Brand::whereNull('parent_id')->count();
        $totalProductsCount = Product::whereNotNull('shop_brand_id')->count();

        $metrics = [
            'total' => $totalCount,
            'visible' => $visibleCount,
            'hidden' => $hiddenCount,
            'root' => $rootCount,
            'total_items' => $totalProductsCount,
        ];

        $allBrands = Brand::withCount('directProducts')
            ->orderBy('order_column')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $groupedBrands = $allBrands->groupBy('parent_id');
        $rootBrands = $groupedBrands->get(null, collect());

        $allBrandIds = $allBrands->pluck('id')->toArray();
        $orphans = $allBrands->filter(function ($item) use ($allBrandIds) {
            return ! is_null($item->parent_id) && ! in_array($item->parent_id, $allBrandIds, true);
        });

        if ($orphans->isNotEmpty()) {
            $rootBrands = $rootBrands->merge($orphans);
        }

        return view('admin.crud.tree', [
            'title' => __($this->title()),
            'allCategories' => $allBrands,
            'groupedCategories' => $groupedBrands,
            'rootCategories' => $rootBrands,
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

    public function toggleStatus(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $brand = Brand::findOrFail($id);
        $newStatus = $request->has('is_visible')
            ? $request->boolean('is_visible')
            : ! $brand->is_visible;

        $brand->is_visible = $newStatus;
        $brand->save();

        $this->flushStorefrontCaches();

        $message = $brand->is_visible
            ? 'Đã bật hiển thị thương hiệu "' . $brand->display_name . '"'
            : 'Đã tạm ẩn thương hiệu "' . $brand->display_name . '"';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_visible' => (bool) $brand->is_visible,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    protected function formData(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        if ($record) {
            return [];
        }

        return [
            'parent_id' => request()->integer('parent_id') ?: null,
        ];
    }
}
