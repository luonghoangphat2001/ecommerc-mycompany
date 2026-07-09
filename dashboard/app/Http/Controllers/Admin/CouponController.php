<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Coupon;

class CouponController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Coupon::class;
    }

    protected function title(): string
    {
        return 'Coupons';
    }

    protected function routePrefix(): string
    {
        return 'admin.coupons';
    }

    protected function fields(): array
    {
        return [
            'code' => ['label' => 'Mã Coupon', 'rules' => ['required', 'string', 'max:50', 'unique:shop_coupons,code,{{id}}']],
            'type' => ['label' => 'Loại', 'type' => 'select', 'rules' => ['required', 'string'], 'options' => ['fixed_cart' => 'Cố định giỏ hàng', 'percentage' => 'Phần trăm', 'fixed_product' => 'Cố định sản phẩm']],
            'value' => ['label' => 'Giá trị', 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
            'description' => ['label' => 'Mô tả', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255']],
            'usage_limit' => ['label' => 'Giới hạn sử dụng', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
            'usage_count' => ['label' => 'Đã sử dụng', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'starts_at' => ['label' => 'Ngày bắt đầu', 'type' => 'date', 'rules' => ['nullable', 'date']],
            'expires_at' => ['label' => 'Ngày hết hạn', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:starts_at']],
        ];
    }
}
