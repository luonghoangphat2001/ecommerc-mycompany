<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Models\UpsellProduct;

class UpsellProductController extends BaseCrudController
{
    use HasAdminSelectOptions;

    protected function modelClass(): string
    {
        return UpsellProduct::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.upsell_products';
    }

    protected function routePrefix(): string
    {
        return 'admin.upsell-products';
    }

    protected function fields(): array
    {
        $productOptions = $this->productOptions();

        return [
            'shop_product_id' => ['label' => 'Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => $productOptions],
            'upsell_product_id' => ['label' => 'Upsell Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => $productOptions],
            'sort_order' => $this->sortOrderField(),
            'is_active' => $this->activeField(),
        ];
    }
}
