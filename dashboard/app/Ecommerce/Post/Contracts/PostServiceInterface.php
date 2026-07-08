<?php

namespace App\Ecommerce\Post\Contracts;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface PostServiceInterface
{
    public function getPaginatedPosts(int $perPage = 10): LengthAwarePaginator;

    public function createPost(array $data): Post;

    public function updatePost(Post $post, array $data): Post;

    public function deletePost(Post $post): bool;

    public function getFeaturedImageUrl(Post $post): ?string;

    public function firstOrNew(array $attributes): Post;

    public function findOrFail(int|string $id): Post;

    public function getTableQuery(): Builder;
}
