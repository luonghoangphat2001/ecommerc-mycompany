<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class PostCategoryRequest extends FormRequest
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
        $cat = $this->route('post_category') ?? $this->route('id');
        $catId = is_object($cat) ? $cat->id : ($cat ?? 'NULL');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || $cat !== null;

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $catId . ',id',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'is_visible' => 'nullable|boolean',
        ];
    }
}
