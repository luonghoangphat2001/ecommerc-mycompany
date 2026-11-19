<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\ApiFormRequest;

class RegisterRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => array_merge(['required', 'string', 'min:6'], $this->filled('password_confirmation') ? ['confirmed'] : []),
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
