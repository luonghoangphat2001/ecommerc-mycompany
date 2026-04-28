<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class PostCategoryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'name' => $this->translate('name'),
            'slug' => $this->slug,
            'description' => $this->translate('description'),
            'is_visible' => $this->is_visible,
            'posts_count' => $this->whenCounted('posts'),
            'posts' => PostResource::collection($this->whenLoaded('posts')),
        ], $this->getTimestamps());
    }
}
