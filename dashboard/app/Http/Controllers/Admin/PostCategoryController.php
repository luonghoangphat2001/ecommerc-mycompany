<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\PostCategory;

class PostCategoryController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return PostCategory::class;
    }

    protected function title(): string
    {
        return 'Post Categories';
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
            'name' => ['label' => 'Name', 'rules' => ['required']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'parent_id' => [
                'label' => 'Parent',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:categories,id'],
                'options' => ['' => '-- Root --'] + PostCategory::orderBy('id')->pluck('slug', 'id')->toArray(),
            ],
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
}
