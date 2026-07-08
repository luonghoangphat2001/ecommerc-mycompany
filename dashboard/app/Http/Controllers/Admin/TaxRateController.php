<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\TaxClass;
use App\Models\TaxRate;

class TaxRateController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return TaxRate::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.rates';
    }

    protected function routePrefix(): string
    {
        return 'admin.tax-rates';
    }

    protected function searchable(): array
    {
        return ['name', 'country', 'state', 'city'];
    }

    protected function fields(): array
    {
        return [
            'tax_class_id' => [
                'label' => 'Tax Class',
                'type' => 'select',
                'rules' => ['required', 'integer', 'exists:shop_tax_classes,id'],
                'options' => TaxClass::orderBy('name')->pluck('name', 'id')->toArray(),
            ],
            'name' => ['label' => 'Tên thuế', 'rules' => ['required', 'string', 'max:255']],
            'country' => ['label' => 'Country', 'rules' => ['nullable', 'string', 'max:2']],
            'state' => ['label' => 'State', 'rules' => ['nullable', 'string', 'max:255']],
            'city' => ['label' => 'City', 'rules' => ['nullable', 'string', 'max:255']],
            'rate' => ['label' => 'Rate %', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
            'priority' => ['label' => 'Priority', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'is_compound' => ['label' => 'Compound', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['0' => 'Không', '1' => 'Có']],
            'is_shipping' => ['label' => 'Shipping Tax', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['0' => 'Không', '1' => 'Có']],
        ];
    }
}
