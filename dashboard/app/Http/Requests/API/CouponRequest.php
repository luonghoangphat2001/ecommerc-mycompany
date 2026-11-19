<?php

namespace App\Http\Requests\API;

class CouponRequest extends ApiFormRequest
{
    /**
     * Validation rules for coupon application and management.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'items' => ['required', 'array'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ];
    }
}
