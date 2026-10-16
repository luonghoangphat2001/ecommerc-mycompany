<?php

namespace App\Http\Requests\API;

use App\Enums\AddressType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'company' => 'nullable|string|max:150',
            'phone' => 'sometimes|string|max:30',
            'email' => 'nullable|email|max:150',
            'type' => ['nullable', 'string', new Enum(AddressType::class)],
            'country_code' => 'sometimes|string|max:2',
            'state_id' => 'nullable|string|max:50',
            'city_id' => 'nullable|string|max:50',
            'ward_id' => 'nullable|string|max:50',
            'street' => 'sometimes|string|max:255',
            'zip' => 'nullable|string|max:20',
        ];
    }
}
