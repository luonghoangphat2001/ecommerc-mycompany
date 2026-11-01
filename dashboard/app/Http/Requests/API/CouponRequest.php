<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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

