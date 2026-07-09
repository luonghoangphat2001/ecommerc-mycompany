<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Tags\Tag;

class PostController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.posts';
    }

    protected function routePrefix(): string
    {
        return 'admin.posts';
    }

    protected function searchable(): array
    {
        return ['title', 'slug', 'post_type'];
    }

    protected function showGroups(): array
    {
        return [
            'general' => [
                'label' => 'Thông tin chung',
                'fields' => ['title', 'slug', 'post_type', 'is_visible', 'published_at'],
            ],
            'content' => [
                'label' => 'Nội dung',
                'fields' => ['content'],
            ],
            'media' => [
                'label' => 'Media',
                'fields' => ['image'],
            ],
            'taxonomy' => [
                'label' => 'Phân loại',
                'fields' => ['categories', 'tags'],
            ],
            'seo' => [
                'label' => 'SEO Metadata',
                'fields' => ['seo_title', 'seo_description'],
            ],
        ];
    }

    protected function fields(): array
    {
        return [
            'author_id' => [
                'label' => 'Author',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:users,id'],
                'options' => ['' => '-- None --'] + User::orderBy('name')->pluck('name', 'id')->toArray(),
                'hideOnIndex' => true,
            ],
            'image' => ['label' => 'Image', 'type' => 'image', 'rules' => ['nullable', 'image', 'max:5120']],
            'title' => ['label' => 'Tiêu đề', 'rules' => ['required']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255'], 'hideOnIndex' => true],
            'content' => ['label' => 'Nội dung', 'type' => 'editor', 'rules' => ['nullable', 'string'], 'hideOnIndex' => true],
            'post_type' => ['label' => 'Type', 'type' => 'select', 'rules' => ['required', 'string', 'max:50'], 'options' => ['blog' => 'blog', 'news' => 'news', 'page' => 'page']],
            'published_at' => ['label' => 'Published At', 'type' => 'date', 'rules' => ['nullable', 'date']],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['required', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255'], 'colspan' => 'full', 'hideOnIndex' => true],
            'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255'], 'hideOnIndex' => true],
            'categories' => [
                'label' => 'Categories',
                'type' => 'tree-select',
                'colspan' => 1,
                'rules' => ['nullable', 'array'],
                'tree_nodes' => PostCategory::whereNull('parent_id')->with('children.children')->get(),
                'hideOnIndex' => true,
            ],
            'tags' => [
                'label' => 'Tags',
                'type' => 'tags-checkboxes',
                'colspan' => 1,
                'rules' => ['nullable', 'array'],
                'options' => Tag::withType('Post')->pluck('name', 'name')->toArray(),
                'hideOnIndex' => true,
            ],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        unset($data['categories'], $data['tags']);

        return parent::mutateData($data, $record);
    }

    protected function afterSave(Model $record, Request $request): void
    {
        $record->categories()->sync((array) $request->input('categories', []));
        $tagsInput = $request->input('tags', []);
        $tags = is_array($tagsInput) ? $tagsInput : collect(explode(',', (string) $tagsInput))->map(fn($tag) => trim($tag))->filter()->values()->all();
        $record->syncTagsWithType($tags, 'Post');
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        $imagePath = null;
        if ($record->image) {
            if (is_numeric($record->image) && $record->featuredImage) {
                $imagePath = $record->featuredImage->path;
            } else {
                $imagePath = $record->image;
            }
        }

        return [
            'categories' => $record->categories()->pluck('categories.id')->toArray(),
            'tags' => $record->tags->pluck('name')->toArray(),
            'image' => $imagePath,
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
            'image' => ['nullable', 'image', 'max:5120'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ];
    }
}
