<?php

namespace App\Ecommerce\Product\DTOs\Brand;

use Illuminate\Http\Request;

class BrandDTO
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $website = null,
        public ?string $description = null,
        public bool $isVisible = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            website: $request->validated('website'),
            description: $request->validated('description'),
            isVisible: $request->boolean('is_visible', true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'website' => $this->website,
            'description' => $this->description,
            'is_visible' => $this->isVisible,
        ];
    }
}
