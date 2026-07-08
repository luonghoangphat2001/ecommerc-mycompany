<?php

namespace App\Http\Requests\API\Brand;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_visible' => 'nullable|boolean',
        ];
    }
}
