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
        ], $this->getTimestamps());
    }
}
