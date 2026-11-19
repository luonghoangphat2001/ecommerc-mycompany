<?php

namespace App\Http\Requests\API;

class PageRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * Handles both Store (POST) and Update (PUT/PATCH).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pageId = $this->route('page') ?? $this->route('id');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH') || ! empty($pageId);

        return [
            'title' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . ($pageId ? (is_object($pageId) ? $pageId->id : $pageId) : 'NULL') . ',id',
            'content' => 'nullable|string',
            'layout' => 'nullable|string|max:50',
        ];
    }
}
