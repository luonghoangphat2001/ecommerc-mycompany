<?php

namespace App\Http\Requests\API;

class AttributeValuesRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'values' => ['required', 'array', 'min:1'],
            'values.*' => ['string'],
        ];
    }
}
