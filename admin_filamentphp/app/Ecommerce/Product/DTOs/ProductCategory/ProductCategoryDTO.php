<?php

namespace App\Ecommerce\Product\DTOs\ProductCategory;

use Illuminate\Http\Request;

class ProductCategoryDTO
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
        public ?int $parentId = null,
        public bool $isVisible = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            parentId: $request->validated('parent_id'),
            isVisible: $request->boolean('is_visible', true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'is_visible' => $this->isVisible,
        ];
    }
}
