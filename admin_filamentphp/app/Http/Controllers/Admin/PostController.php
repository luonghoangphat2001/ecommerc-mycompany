<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function title(): string
    {
        return 'Posts';
    }

    protected function routePrefix(): string
    {
        return 'admin.posts';
    }

    protected function searchable(): array
    {
        return ['title', 'slug', 'post_type'];
    }

    protected function fields(): array
    {
        return [
            'author_id' => [
                'label' => 'Author',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:users,id'],
                'options' => ['' => '-- None --'] + User::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'title' => ['label' => 'Tiêu đề', 'rules' => ['required']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'content' => ['label' => 'Nội dung', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'post_type' => ['label' => 'Type', 'type' => 'select', 'rules' => ['required', 'string', 'max:50'], 'options' => ['blog' => 'blog', 'news' => 'news', 'page' => 'page']],
            'published_at' => ['label' => 'Published At', 'type' => 'date', 'rules' => ['nullable', 'date']],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['required', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'image' => ['label' => 'Image', 'rules' => ['nullable', 'string', 'max:255']],
            'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255']],
            'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255']],
            'categories' => [
                'label' => 'Categories',
                'type' => 'multiselect',
                'rules' => ['nullable', 'array'],
                'options' => PostCategory::orderBy('id')->pluck('slug', 'id')->toArray(),
            ],
            'tags' => ['label' => 'Tags (comma separated)', 'rules' => ['nullable', 'string']],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        unset($data['categories'], $data['tags']);

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $record->categories()->sync((array) $request->input('categories', []));
        $tags = collect(explode(',', (string) $request->input('tags', '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
        $record->syncTags($tags);
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'categories' => $record->categories()->pluck('categories.id')->toArray(),
            'tags' => $record->tags->pluck('name')->implode(', '),
        ];
    }

    protected function rules(?int $id = null): array
    {
        return [
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($id)],
            'content' => ['nullable', 'string'],
            'post_type' => ['required', 'string', 'max:50'],
            'published_at' => ['nullable', 'date'],
            'is_visible' => ['required', 'boolean'],
            'image' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string'],
        ];
    }
}
