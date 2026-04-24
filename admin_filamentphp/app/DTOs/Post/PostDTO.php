<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

class PostDTO
{
    public function __construct(
        public string $title,
        public string $content,
        public ?int $authorId = null,
        public ?string $postType = 'post',
        public ?string $image = null,
        public ?string $publishedAt = null,
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
        public bool $isVisible = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->validated('title'),
            content: $request->validated('content'),
            authorId: $request->user()?->id,
            postType: $request->validated('post_type', 'post'),
            image: $request->validated('image'),
            publishedAt: $request->validated('published_at'),
            seoTitle: $request->validated('seo_title'),
            seoDescription: $request->validated('seo_description'),
            isVisible: $request->boolean('is_visible', true),
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'author_id' => $this->authorId,
            'post_type' => $this->postType,
            'image' => $this->image,
            'published_at' => $this->publishedAt,
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'is_visible' => $this->isVisible,
        ];
    }
}
