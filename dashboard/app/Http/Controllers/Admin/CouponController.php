<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CouponController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Coupon::class;
    }

    protected function title(): string
    {
        return __('admin.coupon.label');
    }

    protected function routePrefix(): string
    {
        return 'admin.coupons';
    }

    protected function searchable(): array
    {
        return ['code'];
    }

    protected function filters(): array
    {
        return [
            'type' => [
                'label' => __('admin.coupon.fields.type'),
                'placeholder' => '-- Tất cả --',
                'options' => [
                    'fixed_cart' => __('admin.coupon.types.fixed_cart'),
                    'percentage' => __('admin.coupon.types.percentage'),
                    'fixed_product' => __('admin.coupon.types.fixed_product'),
                ],
            ],
            'is_active' => [
                'label' => 'Trạng thái',
                'placeholder' => '-- Tất cả --',
                'options' => ['1' => 'Đang bật', '0' => 'Đang tắt'],
            ],
            'expiry_from' => [
                'label' => 'Hết hạn từ ngày',
                'type' => 'date',
            ],
            'expiry_to' => [
                'label' => 'Đến ngày',
                'type' => 'date',
            ],
        ];
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('expiry_from')) {
            $query->whereDate('expiry_date', '>=', $request->query('expiry_from'));
        }

        if ($request->filled('expiry_to')) {
            $query->whereDate('expiry_date', '<=', $request->query('expiry_to'));
        }

        return $query;
    }

    protected function fields(): array
    {
        return [
            'code' => ['label' => __('admin.coupon.fields.code'), 'rules' => ['required', 'string', 'max:50', 'unique:shop_coupons,code,{{id}}']],
            'type' => ['label' => __('admin.coupon.fields.type'), 'type' => 'select', 'rules' => ['required', 'string'], 'options' => ['fixed_cart' => __('admin.coupon.types.fixed_cart'), 'percentage' => __('admin.coupon.types.percentage'), 'fixed_product' => __('admin.coupon.types.fixed_product')]],
            'value' => ['label' => __('admin.coupon.fields.value'), 'type' => 'number', 'rules' => ['required', 'numeric', 'min:0']],
            'description' => ['label' => __('admin.coupon.fields.description'), 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:255']],
            'usage_limit' => ['label' => __('admin.coupon.fields.usage_limit'), 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
            'usage_count' => ['label' => __('admin.coupon.fields.usage_count'), 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'starts_at' => ['label' => __('admin.coupon.fields.starts_at'), 'type' => 'date', 'rules' => ['nullable', 'date']],
            'expires_at' => ['label' => __('admin.coupon.fields.expires_at'), 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:starts_at']],
        ];
    }
}
