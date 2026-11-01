<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for authentication and profile updates.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Profile update rules
        if ($this->isMethod('PUT') || $this->isMethod('PATCH') || $this->has('bio') || $this->has('current_password') || $this->has('birthday')) {
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

        // Login rules
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }
}

