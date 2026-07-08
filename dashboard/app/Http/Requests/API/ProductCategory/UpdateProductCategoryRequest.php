<?php

namespace App\Http\Requests\API\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:shop_categories,slug,' . $this->route('product_category'),
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:shop_categories,id',
            'is_visible' => 'nullable|boolean',
        ];
    }
}
