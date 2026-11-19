<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends BaseCrudController
{
    use HasAdminSelectOptions;

    protected function modelClass(): string
    {
        return Comment::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.comments';
    }

    protected function routePrefix(): string
    {
        return 'admin.comments';
    }

    protected function searchable(): array
    {
        return ['title', 'content', 'commentable_type'];
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query->with(['user', 'commentable'])->latest('id');
    }

    protected function fields(): array
    {
        return [
            'commentable_label' => [
                'label' => __('admin.comments.target') ?: 'Target',
                'virtual' => true,
                'tableOnly' => true,
                'hideOnForm' => true,
                'hideOnExport' => true,
                'hideOnImport' => true,
            ],
            'user_id' => [
                'label' => __('admin.comments.user') ?: 'User',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:users,id'],
                'options' => ['' => '-- ' . __('admin.common.none', ['default' => 'Không chọn']) . ' --'] + $this->userOptions(),
            ],
            'title' => ['label' => __('fields.title') ?: 'Title', 'rules' => ['nullable', 'string', 'max:255']],
            'content' => ['label' => __('fields.content') ?: 'Content', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'is_visible' => [
                'label' => __('admin.fields.is_visible'),
                'type' => 'select',
                'rules' => ['nullable', 'boolean'],
                'options' => [
                    '1' => __('admin.common.yes') . ' (Hiển thị)',
                    '0' => __('admin.common.no') . ' (Tạm ẩn)',
                ],
            ],
        ];
    }

    public function index(Request $request): View
    {
        $query = $this->indexQuery(Comment::query(), $request);
        $this->applySearch($query, $request);

        return view('admin.comments.index', [
            'title' => 'Comments',
            'items' => $this->paginateIndex($query),
            'routePrefix' => $this->routePrefix(),
        ]);
    }
}
