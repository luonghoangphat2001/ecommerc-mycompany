<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasHierarchicalOptions;
use App\Http\Controllers\Admin\Traits\HasVisibilityMetrics;
use App\Http\Controllers\Admin\Traits\HasVisibilityToggle;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PostCategoryController extends BaseCrudController
{
    use HasHierarchicalOptions;
    use HasVisibilityMetrics;
    use HasVisibilityToggle;

    protected function visibilityEntityLabel(): string
    {
        return 'danh mục bài viết';
    }

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
        $totalPostsCount = DB::table('post_category')->distinct('post_id')->count('post_id');

        $metrics = $this->visibilityMetrics([
            'total_items' => $totalPostsCount,
        ]);

        $allCategories = PostCategory::withCount('posts')
            ->orderBy('order_column')
            ->orderBy('id')
            ->get();

        $hierarchy = $this->hierarchyCollections($allCategories);

        return view('admin.crud.tree', [
            'title' => __($this->title()),
            'allCategories' => $allCategories,
            'groupedCategories' => $hierarchy['grouped'],
            'rootCategories' => $hierarchy['roots'],
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

    protected function formData(?Model $record = null): array
    {
        return [
            'parent_id' => request()->integer('parent_id') ?: null,
            'type' => 'post',
        ];
    }
}
