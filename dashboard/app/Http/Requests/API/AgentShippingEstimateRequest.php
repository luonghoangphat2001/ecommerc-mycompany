<?php

namespace App\Http\Requests\API;

class AgentShippingEstimateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'country' => ['nullable', 'string', 'max:10'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
            'subtotal' => ['nullable', 'integer', 'min:0'],
            'items' => ['nullable', 'array'],
            'weight' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
