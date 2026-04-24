<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'blog_author_id' => 'required|exists:blog_authors,id',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
