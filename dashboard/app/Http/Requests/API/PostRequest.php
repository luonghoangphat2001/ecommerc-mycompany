<?php

namespace App\Http\Requests\API;

class PostRequest extends ApiFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * Handles both Store (POST) and Update (PUT/PATCH).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('POST') ? 'required' : 'sometimes|required';

        return [
            'title' => "{$required}|string|max:255",
            'content' => "{$required}|string",
            'post_type' => 'nullable|string|max:50',
            'image' => 'nullable|string',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'is_visible' => 'nullable|boolean',
        ];
    }
}
