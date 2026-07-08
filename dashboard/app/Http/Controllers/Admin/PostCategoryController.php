<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query->with(['children.children']);
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Name', 'rules' => ['required']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'parent_id' => [
                'label' => 'Parent',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:categories,id'],
                'options' => ['' => '-- Root --'] + PostCategory::orderBy('id')->pluck('slug', 'id')->toArray(),
            ],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'type' => ['label' => 'Type', 'rules' => ['nullable', 'string', 'max:50']],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
        ];
    }

    protected function mutateData(array $data, ?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        return $data;
    }

    public function index(Request $request): View
    {
        $query = PostCategory::query()->with(['children.children']);

        return view('admin.categories.tree', [
            'title' => 'Post Categories',
            'items' => $query->latest('id')->get(),
            'routePrefix' => $this->routePrefix(),
            'entityLabel' => 'category',
            'entityPlural' => 'categories',
            'createLabel' => 'Thêm category',
        ]);
    }

    public function create(): View
    {
        return view('admin.crud.form', [
            'title' => 'Tạo mới - ' . $this->title(),
            'record' => null,
            'fields' => $this->visibleFields('form'),
            'routePrefix' => $this->routePrefix(),
            'formData' => [
                'parent_id' => request()->integer('parent_id') ?: null,
                'type' => 'post',
            ],
        ]);
    }
}
