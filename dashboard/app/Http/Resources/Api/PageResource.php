<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class PageResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'layout' => $this->layout,
            'parent_id' => $this->parent_id ?? null,
            'blocks' => $this->blocks ?? [],
        ], $this->getTimestamps());
    }
}
