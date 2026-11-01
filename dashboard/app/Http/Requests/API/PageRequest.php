<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
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
        $pageId = $this->route('page') ?? $this->route('id');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || !empty($pageId);

        return [
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . ($pageId ? (is_object($pageId) ? $pageId->id : $pageId) : 'NULL') . ',id',
            'content' => 'nullable|string',
            'layout' => 'nullable|string|max:50',
        ];
    }
}
