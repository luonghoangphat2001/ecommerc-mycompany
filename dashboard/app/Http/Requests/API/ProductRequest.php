<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
     * Handles both Store (POST) and Update (PUT/PATCH).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : ($product ?? 'NULL');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $product !== null;

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'description' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'shop_brand_id' => 'nullable|exists:shop_brands,id',
            'sku' => 'nullable|string|unique:shop_products,sku,' . $productId . ',id',
            'price' => 'nullable|numeric|min:0',
            'qty' => 'nullable|integer|min:0',
            'is_visible' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
        ];
    }
}
