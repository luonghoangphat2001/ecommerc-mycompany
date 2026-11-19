<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\ApiFormRequest;

class ResetPasswordRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'token' => ['required', 'string', 'size:64'],
        ];
    }
}
