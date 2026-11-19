<?php

namespace App\Http\Requests\API\Auth;

use App\Http\Requests\API\ApiFormRequest;

class ForgotPasswordRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
