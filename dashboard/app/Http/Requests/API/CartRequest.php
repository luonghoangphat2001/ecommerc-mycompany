<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Handles sync/calculate, add to cart, and update item.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // 1. Bulk sync/calculate cart
        if ($this->has('items') || $this->has('country') || $this->has('shipping_method')) {
            return [
                'items' => ['nullable', 'array'],
                'items.*.product_id' => ['required_without:items.*.id', 'integer'],
                'items.*.id' => ['required_without:items.*.product_id', 'integer'],
                'items.*.variant_id' => ['nullable', 'integer'],
                'items.*.quantity' => ['nullable', 'integer', 'min:1'],
                'items.*.qty' => ['nullable', 'integer', 'min:1'],
                'items.*.price' => ['nullable', 'numeric', 'min:0'],
                'country' => ['nullable', 'string', 'max:5'],
                'state' => ['nullable', 'string', 'max:100'],
                'shipping_method' => ['nullable', 'string', 'max:100'],
                'coupon_code' => ['nullable', 'string', 'max:50'],
            ];
        }

        // 2. Add item to cart
        if ($this->has('product_id')) {
            return [
                'product_id' => ['required', 'integer', 'exists:shop_products,id'],
                'variant_id' => ['nullable', 'integer', 'exists:shop_product_variants,id'],
                'quantity' => ['required', 'integer', 'min:1'],
            ];
        }

        // 3. Update single item quantity
        return [
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
