<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends BaseCrudController
{
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
                'label' => 'Target',
                'virtual' => true,
                'tableOnly' => true,
                'hideOnForm' => true,
                'hideOnExport' => true,
                'hideOnImport' => true,
            ],
            'user_id' => [
                'label' => 'User',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:users,id'],
                'options' => ['' => '-- None --'] + User::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'title' => ['label' => 'Title', 'rules' => ['nullable', 'string', 'max:255']],
            'content' => ['label' => 'Content', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'is_visible' => ['label' => 'Visible', 'type' => 'boolean', 'rules' => ['nullable', 'boolean']],
        ];
    }

    public function index(Request $request): View
    {
        $query = Comment::query()->with(['user', 'commentable']);
        $this->applySearch($query, $request);

        return view('admin.comments.index', [
            'title' => 'Comments',
            'items' => $query->latest('id')->paginate(15)->withQueryString(),
            'routePrefix' => $this->routePrefix(),
        ]);
    }
}
