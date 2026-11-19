<?php

namespace App\Http\Requests\API;

class TaxRequest extends ApiFormRequest
{
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
