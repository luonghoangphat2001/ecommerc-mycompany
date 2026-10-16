<?php

namespace App\Http\Requests\API\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'items' => ['required', 'array'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ];
    }
}

