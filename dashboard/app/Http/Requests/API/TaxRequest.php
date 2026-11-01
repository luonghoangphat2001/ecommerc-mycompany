<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class TaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for tax requests.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'integer', 'exists:shop_products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:shop_product_variants,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'country' => ['nullable', 'string', 'max:5'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }
}
