<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends BaseCrudController
{
    public function index(Request $request): View
    {
        $query = Brand::query();

        return view('admin.crud.tree', [
            'title' => 'Thương hiệu',
            'items' => $query->latest('id')->get(),
            'routePrefix' => $this->routePrefix(),
            'canCreate' => true,
            'canEdit' => true,
            'canDelete' => true,
            'maxDepth' => 2,
        ]);
    }

    protected function indexQuery(\Illuminate\Database\Eloquent\Builder $query, Request $request): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with(['children.children']);
    }

    protected function modelClass(): string
    {
        return Brand::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.brands';
    }

    protected function routePrefix(): string
    {
        return 'admin.brands';
    }

    protected function searchable(): array
    {
        return ['name', 'slug', 'website'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Name', 'rules' => ['required']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
            'parent_id' => [
                'label' => 'Thương hiệu cha',
                'type' => 'select',
                'rules' => ['nullable', 'integer', 'exists:shop_brands,id'],
                'options' => ['' => '-- Trống --'] + Brand::orderBy('id')->pluck('name', 'id')->toArray(),
            ],
            'website' => ['label' => 'Website', 'rules' => ['nullable', 'url']],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
        ];
    }
}
