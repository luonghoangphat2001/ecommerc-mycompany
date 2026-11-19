<?php

namespace App\Http\Requests\API;

class LoyaltyHistoryRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => ['integer', 'min:1', 'max:100'],
            'page' => ['integer', 'min:1'],
        ];
    }
}
