<?php

namespace App\Http\Requests\API;

class AttributeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [$this->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:select,button,color'],
            'values' => ['nullable', 'array'],
            'values.*' => ['string'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }
}
