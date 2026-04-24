<?php

namespace App\Http\Requests\API\Brand;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $this->route('brand'),
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_visible' => 'nullable|boolean',
        ];
    }
}
