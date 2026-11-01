<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Handles both Store (POST) and Update (PUT/PATCH).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $brand = $this->route('brand');
        $brandId = is_object($brand) ? $brand->id : ($brand ?? 'NULL');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $brand !== null;

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brandId . ',id',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_visible' => 'nullable|boolean',
        ];
    }
}
