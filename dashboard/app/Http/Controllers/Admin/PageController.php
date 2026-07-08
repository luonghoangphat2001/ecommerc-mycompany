<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Page;

class PageController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Page::class;
    }

    protected function title(): string
    {
        return 'Pages';
    }

    protected function routePrefix(): string
    {
        return 'admin.pages';
    }

    protected function searchable(): array
    {
        return ['title', 'slug', 'layout'];
    }

    protected function fields(): array
    {
        return [
            'title' => ['label' => 'Title', 'rules' => ['required', 'string', 'max:255']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'layout' => ['label' => 'Layout', 'rules' => ['required', 'string', 'max:100']],
            'parent_id' => [
                'label' => 'Parent',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:pages,id'],
                'options' => ['' => '-- Root --'] + Page::orderBy('id')->pluck('title', 'id')->toArray(),
            ],
            'blocks' => ['label' => 'Blocks JSON', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
        ];
    }

    protected function mutateData(array $data, ?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        if (isset($data['blocks']) && is_string($data['blocks'])) {
            $decoded = json_decode($data['blocks'], true);
            $data['blocks'] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        return $data;
    }

    protected function formData(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        if (! $record) {
            return ['blocks' => '[]'];
        }

        return [
            'blocks' => json_encode($record->blocks ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}
