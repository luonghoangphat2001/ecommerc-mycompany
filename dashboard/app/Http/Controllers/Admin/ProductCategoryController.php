<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasHierarchicalOptions;
use App\Http\Controllers\Admin\Traits\HasVisibilityMetrics;
use App\Http\Controllers\Admin\Traits\HasVisibilityToggle;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductCategoryController extends BaseCrudController
{
    use HasHierarchicalOptions;
    use HasVisibilityMetrics;
    use HasVisibilityToggle;

    protected function modelClass(): string
    {
        return ProductCategory::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.product_categories';
    }

    protected function routePrefix(): string
    {
        return 'admin.product-categories';
    }

    protected function searchable(): array
    {
        return ['name', 'slug'];
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
                'rules' => ['nullable', 'integer', 'exists:shop_categories,id'],
                'options' => $this->getParentCategoryOptions(request()->route('product_category')?->id ?? request()->route('product_category')),
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
            'seo_title' => [
                'label' => 'SEO Title (Tiêu đề SEO)',
                'rules' => ['nullable', 'string', 'max:255'],
            ],
            'seo_description' => [
                'label' => 'SEO Description (Mô tả SEO)',
                'type' => 'textarea',
                'rules' => ['nullable', 'string'],
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
        // 1. Calculate Shopify KPI Metrics
        $hasProductsCount = ProductCategory::query()->has('products')->count();
        $totalProductsCount = DB::table('shop_category_product')->distinct('shop_product_id')->count('shop_product_id');

        $metrics = $this->visibilityMetrics([
            'has_products' => $hasProductsCount,
            'total_items' => $totalProductsCount,
        ]);

        // 2. Fetch all categories with products count, ordered properly
        $allCategories = ProductCategory::withCount('products')
            ->orderBy('order_column')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        // 3. Group by parent_id for recursive hierarchy tree building
        $hierarchy = $this->hierarchyCollections($allCategories);

        return view('admin.crud.tree', [
            'title' => __($this->title()),
            'allCategories' => $allCategories,
            'groupedCategories' => $hierarchy['grouped'],
            'rootCategories' => $hierarchy['roots'],
            'metrics' => $metrics,
            'routePrefix' => $this->routePrefix(),
            'entityLabel' => 'danh mục',
            'entityPlural' => 'danh mục sản phẩm',
            'itemCountRelation' => 'products_count',
            'itemCountLabel' => 'sp',
            'itemCountRoute' => 'admin.products.index',
            'itemCountParam' => 'category_id',
            'itemCountIcon' => 'package',
            'createLabel' => __('admin.actions.create'),
        ]);
    }

    public function bulkAction(Request $request): JsonResponse | RedirectResponse
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids) || ! is_array($ids)) {
            $msg = 'Vui lòng chọn ít nhất một danh mục để thao tác.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('status', $msg);
        }

        $count = count($ids);
        $message = '';

        if ($action === 'visible') {
            ProductCategory::whereIn('id', $ids)->update(['is_visible' => true]);
            $message = "Đã hiển thị {$count} danh mục thành công.";
        } elseif ($action === 'hidden') {
            ProductCategory::whereIn('id', $ids)->update(['is_visible' => false]);
            $message = "Đã ẩn {$count} danh mục thành công.";
        } elseif ($action === 'delete') {
            ProductCategory::whereIn('parent_id', $ids)->update(['parent_id' => null]);
            ProductCategory::whereIn('id', $ids)->delete();
            $message = "Đã xóa {$count} danh mục thành công.";
        } else {
            $msg = 'Hành động không hợp lệ.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('status', $msg);
        }

        $this->flushStorefrontCaches();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('status', $message);
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
