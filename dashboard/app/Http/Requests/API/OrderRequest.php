<?php

namespace App\Http\Requests\API;

use App\Ecommerce\Order\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class OrderRequest extends FormRequest
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
     * Handles both Store (POST checkout) and Update (PUT/PATCH status/notes).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return [
                'status' => ['sometimes', 'string', new Enum(OrderStatus::class)],
                'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            ];
        }

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
            'items.*.variant_id' => 'nullable|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_breakdown' => 'nullable|array',
            'shipping_method' => 'nullable|string',
            'shipping_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'coupon_code' => 'nullable|string',
            'grand_total' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|size:3',
        ];
    }
}
