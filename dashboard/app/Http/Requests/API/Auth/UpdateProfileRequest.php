<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'gender' => ['sometimes', 'nullable', 'string', 'in:male,female,other'],
            'birthday' => ['sometimes', 'nullable', 'date'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'default_shipping_address_id' => ['sometimes', 'nullable', 'exists:addresses,id'],
            'default_billing_address_id' => ['sometimes', 'nullable', 'exists:addresses,id'],
        ];
    }
}
