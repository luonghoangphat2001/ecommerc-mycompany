<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'shop_brand_id' => 'nullable|exists:shop_brands,id',
            'sku' => 'nullable|string|unique:shop_products,sku',
            'price' => 'nullable|numeric|min:0',
            'qty' => 'nullable|integer|min:0',
            'is_visible' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
        ];
    }
}
