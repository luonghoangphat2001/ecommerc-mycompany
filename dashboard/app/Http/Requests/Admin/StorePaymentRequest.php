<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Validation\Rules\Enum;

class StorePaymentRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'method' => ['required', new Enum(PaymentMethod::class)],
            'amount' => ['required', 'integer', 'min:0'],
            'status' => ['required', new Enum(PaymentStatus::class)],
            'provider' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
