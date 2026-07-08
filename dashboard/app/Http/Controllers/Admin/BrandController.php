<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Brand;

class BrandController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Brand::class;
    }

    protected function title(): string
    {
        return 'Brands';
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
