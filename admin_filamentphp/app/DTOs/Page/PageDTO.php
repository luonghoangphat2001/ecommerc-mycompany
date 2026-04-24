<?php

namespace App\DTOs\Page;

use Illuminate\Http\Request;

class PageDTO
{
    public function __construct(
        public string $title,
        public ?string $slug = null,
        public ?string $content = null,
        public ?string $layout = 'default',
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->validated('title'),
            slug: $request->validated('slug'),
            content: $request->validated('content'),
            layout: $request->validated('layout', 'default'),
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'layout' => $this->layout,
        ];
    }
}
