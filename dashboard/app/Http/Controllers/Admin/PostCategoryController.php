<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PostCategoryController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return PostCategory::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.post_categories';
    }

    protected function routePrefix(): string
    {
        return 'admin.post-categories';
    }

    protected function searchable(): array
    {
        return ['name', 'slug', 'type'];
    }

    public function getParentCategoryOptions(?int $excludeId = null): array
    {
        $categories = PostCategory::orderBy('order_column')->orderBy('id')->get();
        $options = ['' => '-- ' . __('admin.common.none') . ' (Danh mục gốc) --'];

        $buildTree = function ($parentId, $prefix = '') use (&$buildTree, &$options, $categories, $excludeId) {
            foreach ($categories->where('parent_id', $parentId) as $category) {
                if ($excludeId && $category->id == $excludeId) {
                    continue;
                }
                $name = $category->getTranslation('name', 'vi') ?: $category->slug;
                $options[$category->id] = $prefix ? ($prefix . '↳ ' . $name) : $name;
                $buildTree($category->id, $prefix . '    ');
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
                'rules' => ['nullable', 'integer', 'exists:categories,id'],
                'options' => $this->getParentCategoryOptions(request()->route('post_category')?->id ?? request()->route('post_category')),
            ],
            'description' => [
                'label' => __('admin.fields.description'),
                'type' => 'textarea',
                'rules' => ['nullable'],
            ],
            'type' => [
                'label' => __('admin.fields.type'),
                'rules' => ['nullable', 'string', 'max:50'],
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
        $totalCount = PostCategory::count();
        $visibleCount = PostCategory::where('is_visible', true)->count();
        $hiddenCount = PostCategory::where('is_visible', false)->count();
        $rootCount = PostCategory::whereNull('parent_id')->count();
        $totalPostsCount = DB::table('post_category')->distinct('post_id')->count('post_id');

        $metrics = [
            'total' => $totalCount,
            'visible' => $visibleCount,
            'hidden' => $hiddenCount,
            'root' => $rootCount,
            'total_items' => $totalPostsCount,
        ];

        $allCategories = PostCategory::withCount('posts')
            ->orderBy('order_column')
            ->orderBy('id')
            ->get();

        $groupedCategories = $allCategories->groupBy('parent_id');
        $rootCategories = $groupedCategories->get(null, collect());

        $allCategoryIds = $allCategories->pluck('id')->toArray();
        $orphans = $allCategories->filter(function ($item) use ($allCategoryIds) {
            return ! is_null($item->parent_id) && ! in_array($item->parent_id, $allCategoryIds, true);
        });

        if ($orphans->isNotEmpty()) {
            $rootCategories = $rootCategories->merge($orphans);
        }

        return view('admin.crud.tree', [
            'title' => __($this->title()),
            'allCategories' => $allCategories,
            'groupedCategories' => $groupedCategories,
            'rootCategories' => $rootCategories,
            'metrics' => $metrics,
            'routePrefix' => $this->routePrefix(),
            'entityLabel' => 'danh mục bài viết',
            'entityPlural' => 'danh mục bài viết',
            'itemCountRelation' => 'posts_count',
            'itemCountLabel' => 'bài viết',
            'itemCountRoute' => 'admin.posts.index',
            'itemCountParam' => 'category_id',
            'itemCountIcon' => 'file-text',
            'createLabel' => __('admin.actions.create'),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $category = PostCategory::findOrFail($id);
        $newStatus = $request->has('is_visible')
            ? $request->boolean('is_visible')
            : ! $category->is_visible;

        $category->is_visible = $newStatus;
        $category->save();

        $this->flushStorefrontCaches();

        $message = $category->is_visible
            ? 'Đã bật hiển thị danh mục bài viết "' . $category->display_name . '"'
            : 'Đã tạm ẩn danh mục bài viết "' . $category->display_name . '"';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_visible' => (bool) $category->is_visible,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    protected function formData(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        return [
            'parent_id' => request()->integer('parent_id') ?: null,
            'type' => 'post',
        ];
    }
}
