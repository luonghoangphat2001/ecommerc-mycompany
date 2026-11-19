<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'required' => __('validation.required'),
            'email' => __('validation.email'),
            'string' => __('validation.string'),
            'integer' => __('validation.integer'),
            'numeric' => __('validation.numeric'),
            'array' => __('validation.array'),
            'boolean' => __('validation.boolean'),
            'date' => __('validation.date'),
            'exists' => __('validation.exists'),
            'unique' => __('validation.unique'),
            'confirmed' => __('validation.confirmed'),
        ];
    }
}
