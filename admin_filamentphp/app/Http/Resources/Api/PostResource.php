<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class PostResource extends BaseResource
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
            'title' => $this->translate('title'),
            'slug' => $this->slug,
            'content' => $this->translate('content'),
            'post_type' => $this->post_type,
            'image' => $this->image,
            'published_at' => $this->published_at,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'is_visible' => $this->is_visible,
            'author' => $this->whenLoaded('author'),
            'categories' => PostCategoryResource::collection($this->whenLoaded('categories')),
            'comments' => $this->whenLoaded('comments'),
        ], $this->getTimestamps());
    }
}
