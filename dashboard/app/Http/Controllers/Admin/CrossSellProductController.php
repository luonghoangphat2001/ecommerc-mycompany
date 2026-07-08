<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\CrossSellProduct;
use App\Models\Product;

class CrossSellProductController extends BaseCrudController
{
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
        return [
            'shop_product_id' => ['label' => 'Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => Product::orderBy('name')->pluck('name', 'id')->toArray()],
            'cross_sell_product_id' => ['label' => 'Cross-sell Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => Product::orderBy('name')->pluck('name', 'id')->toArray()],
            'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'is_active' => ['label' => 'Kích hoạt', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
        ];
    }
}
