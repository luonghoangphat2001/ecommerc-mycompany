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
            'maxDepth' => 1,
        ]);
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
            'website' => ['label' => 'Website', 'rules' => ['nullable', 'url']],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string']],
            'is_visible' => ['label' => 'Hiển thị', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
        ];
    }
}
