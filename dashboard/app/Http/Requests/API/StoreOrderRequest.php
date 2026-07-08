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
            'shipping_address.phone' => 'required',
            'shipping_address.country' => 'required',
            'shipping_address.street' => 'required',
            'shipping_address.city' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:shop_products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric',
            'shipping_method' => 'required|string',
            'payment_method' => 'required|string',
            'currency' => 'nullable|string|size:3',
        ];
    }
}
