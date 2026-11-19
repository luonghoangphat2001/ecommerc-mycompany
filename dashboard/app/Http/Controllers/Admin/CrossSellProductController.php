<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasAdminSelectOptions;
use App\Models\CrossSellProduct;

class CrossSellProductController extends BaseCrudController
{
    use HasAdminSelectOptions;

    protected function modelClass(): string
    {
        return CrossSellProduct::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.cross_sell_products';
    }

    protected function routePrefix(): string
    {
        return 'admin.cross-sell-products';
    }

    protected function fields(): array
    {
        $productOptions = $this->productOptions();

        return [
            'shop_product_id' => ['label' => 'Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => $productOptions],
            'cross_sell_product_id' => ['label' => 'Cross-sell Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => $productOptions],
            'sort_order' => $this->sortOrderField(),
            'is_active' => $this->activeField(),
        ];
    }
}
