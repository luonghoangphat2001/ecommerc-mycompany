<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryController extends BaseCrudController
{
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
                'rules' => ['nullable', 'integer', 'exists:shop_categories,id'],
                'options' => ['' => '-- Root --'] + ProductCategory::orderBy('id')->pluck('slug', 'id')->toArray(),
            ],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'sort' => ['label' => 'Sort', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'type' => ['label' => 'Type', 'rules' => ['nullable', 'string', 'max:50']],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
            'seo_title' => ['label' => 'SEO Title', 'rules' => ['nullable', 'string', 'max:255']],
            'seo_description' => ['label' => 'SEO Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
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
        $query = ProductCategory::query()->with(['children.children']);

        return view('admin.categories.tree', [
            'title' => 'Product Categories',
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
                'type' => 'product',
            ],
        ]);
    }
}
