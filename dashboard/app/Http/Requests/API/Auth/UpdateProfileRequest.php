<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\ApiFormRequest;

class UpdateProfileRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'gender' => ['sometimes', 'nullable', 'string', 'in:male,female,other'],
            'birthday' => ['sometimes', 'nullable', 'date'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'avatar' => ['sometimes', 'nullable', 'file', 'image', 'max:5120'],
            'photo' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'current_password' => ['sometimes', 'nullable', 'string'],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'password_confirmation' => ['sometimes', 'nullable', 'string'],
            'default_shipping_address_id' => ['sometimes', 'nullable', 'exists:addresses,id'],
            'default_billing_address_id' => ['sometimes', 'nullable', 'exists:addresses,id'],
        ];
    }
}
