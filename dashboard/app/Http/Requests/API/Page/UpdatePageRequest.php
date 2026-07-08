<?php

namespace App\Http\Requests\API\Page;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $this->route('page'),
            'content' => 'nullable|string',
            'layout' => 'nullable|string|max:50',
        ];
    }
}
