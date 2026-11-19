<?php

namespace App\Http\Requests\Admin;

class CarrierUpdateRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'enabled' => ['nullable', 'boolean'],
            'sandbox' => ['nullable', 'boolean'],
            'api_token' => ['nullable', 'string'],
            'shop_id' => ['nullable', 'string'],
            'client_id' => ['nullable', 'string'],
            'client_secret' => ['nullable', 'string'],
            'markup_amount' => ['nullable', 'integer', 'min:0'],
            'markup_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fallback_rate' => ['nullable', 'integer', 'min:0'],
            'sender_province' => ['nullable', 'string'],
            'sender_district' => ['nullable', 'string'],
        ];
    }
}
