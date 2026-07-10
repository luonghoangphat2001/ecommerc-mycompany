<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We'll use Policies later
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'phone' => 'required',
            'shipping_address' => 'required|array',
            'shipping_address.first_name' => 'required',
            'shipping_address.last_name' => 'required',
            'shipping_address.email' => 'nullable|email',
            'shipping_address.phone' => 'required',
            'shipping_address.country' => 'required',
            'shipping_address.street' => 'required',
            'shipping_address.city' => 'required',
            'shipping_address.state' => 'nullable|string',
            'shipping_address.region' => 'nullable|string',
            'shipping_address.sub_region' => 'nullable|string',
            'shipping_address.address_line_2' => 'nullable|string',
            'billing_address' => 'nullable|array',
            'billing_address.first_name' => 'nullable|string',
            'billing_address.last_name' => 'nullable|string',
            'billing_address.email' => 'nullable|email',
            'billing_address.phone' => 'nullable|string',
            'billing_address.country' => 'nullable|string',
            'billing_address.street' => 'nullable|string',
            'billing_address.city' => 'nullable|string',
            'billing_address.state' => 'nullable|string',
            'billing_address.region' => 'nullable|string',
            'billing_address.sub_region' => 'nullable|string',
            'billing_address.address_line_2' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:shop_products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric',
            'shipping_method' => 'required|string',
            'payment_method' => 'required|string',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'shipping_fee' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'grand_total' => 'required|numeric',
        ];
    }
}
