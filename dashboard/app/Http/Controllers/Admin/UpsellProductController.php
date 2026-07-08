<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Product;
use App\Models\UpsellProduct;

class UpsellProductController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return UpsellProduct::class;
    }

    protected function title(): string
    {
        return 'Upsell Products';
    }

    protected function routePrefix(): string
    {
        return 'admin.upsell-products';
    }

    protected function fields(): array
    {
        return [
            'shop_product_id' => ['label' => 'Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => Product::orderBy('name')->pluck('name', 'id')->toArray()],
            'upsell_product_id' => ['label' => 'Upsell Product', 'type' => 'select', 'rules' => ['required', 'integer', 'exists:shop_products,id'], 'options' => Product::orderBy('name')->pluck('name', 'id')->toArray()],
            'sort_order' => ['label' => 'Sort order', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'is_active' => ['label' => 'Kích hoạt', 'type' => 'select', 'rules' => ['nullable', 'boolean'], 'options' => ['1' => 'Có', '0' => 'Không']],
        ];
    }
}
