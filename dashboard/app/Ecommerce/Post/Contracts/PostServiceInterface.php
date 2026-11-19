<?php

namespace App\Ecommerce\Post\Contracts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

interface PostServiceInterface
{
    public function getPaginatedPosts(int $perPage = 10, array $filters = []): LengthAwarePaginator;

    public function createPost(array $data): Post;

    public function updatePost(Post $post, array $data): Post;

    public function deletePost(Post $post): bool;

    public function getFeaturedImageUrl(Post $post): ?string;

    public function firstOrNew(array $attributes): Post;

    public function findOrFail(int | string $id): Post;

    public function getFeaturedPosts(int $limit = 4);

    public function getTableQuery(): Builder;
}
