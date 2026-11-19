<?php

namespace App\Http\Requests\API;

class GenerateVariantsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'attributes' => ['required', 'array', 'min:1'],
            'attributes.*.name' => ['required', 'string'],
            'attributes.*.options' => ['required', 'array', 'min:1'],
            'attributes.*.options.*' => ['string'],
            'base_sku' => ['nullable', 'string'],
            'base_price' => ['nullable', 'integer'],
        ];
    }
}
