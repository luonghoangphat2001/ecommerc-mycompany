<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\TaxClass;

class TaxClassController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return TaxClass::class;
    }

    protected function title(): string
    {
        return 'Tax Classes';
    }

    protected function routePrefix(): string
    {
        return 'admin.tax-classes';
    }

    protected function searchable(): array
    {
        return ['name', 'slug'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Tên nhóm thuế', 'rules' => ['required', 'string', 'max:255']],
            'slug' => ['label' => 'Slug', 'rules' => ['required', 'string', 'max:255']],
        ];
    }
}
