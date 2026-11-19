<?php

namespace App\Http\Requests\Admin;

use App\Ecommerce\Order\Enums\OrderStatus;
use App\Enums\RefundType;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(OrderStatus::class)],
            'items' => ['array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'internal_note' => ['nullable', 'string'],
            'shipping_method_id' => ['nullable', 'integer', 'exists:shop_shipping_methods,id'],
            'coupon_code' => ['nullable', 'string', 'exists:shop_coupons,code'],
            'redeemed_points' => ['nullable', 'integer', 'min:0'],
            'manual_tax_amount' => ['nullable', 'integer', 'min:0'],
            'refund_type' => ['nullable', new Enum(RefundType::class)],
            'refund_amount' => ['nullable', 'integer', 'min:0'],
            'refund_reason' => ['nullable', 'string'],
            'shipping.first_name' => ['nullable', 'string'],
            'shipping.last_name' => ['nullable', 'string'],
            'shipping.phone' => ['nullable', 'string'],
            'shipping.email' => ['nullable', 'email'],
            'shipping.address_detail' => ['nullable', 'string'],
            'shipping.address_line_2' => ['nullable', 'string'],
            'shipping.city_id' => ['nullable', 'string'],
            'shipping.state_id' => ['nullable', 'string'],
            'shipping.country_code' => ['nullable', 'string'],
            'shipping.postal_code' => ['nullable', 'string'],
            'billing.first_name' => ['nullable', 'string'],
            'billing.last_name' => ['nullable', 'string'],
            'billing.phone' => ['nullable', 'string'],
            'billing.email' => ['nullable', 'email'],
            'billing.address_detail' => ['nullable', 'string'],
            'billing.address_line_2' => ['nullable', 'string'],
            'billing.city_id' => ['nullable', 'string'],
            'billing.state_id' => ['nullable', 'string'],
            'billing.country_code' => ['nullable', 'string'],
            'billing.postal_code' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'coupon_code.exists' => __('admin.order.coupon_not_found'),
        ];
    }
}
