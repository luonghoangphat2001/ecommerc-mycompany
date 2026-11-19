<?php

namespace App\Ecommerce\Post\Services;

use App\Ecommerce\Core\Helpers\CatalogQueryFilters;
use App\Ecommerce\Post\Contracts\PostRepositoryInterface;
use App\Ecommerce\Post\Contracts\PostServiceInterface;
use App\Models\Post;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService implements PostServiceInterface
{
    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * PostService constructor.
     */
    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * Get all posts with relations.
     */
    public function getPaginatedPosts(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = $this->postRepository->query()
            ->with(['author', 'categories', 'comments', 'featuredImage']);

        CatalogQueryFilters::visibility($query, $filters);

        $search = CatalogQueryFilters::searchTerm($filters);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $categoryFilter = $filters['category_id'] ?? $filters['category'] ?? null;
        CatalogQueryFilters::relation($query, $categoryFilter, 'categories', 'categories.id', 'categories.slug');

        return $query->orderByDesc('published_at')->orderByDesc('id')->paginate($perPage);
    }

    public function getFeaturedPosts(int $limit = 4)
    {
        return $this->postRepository->query()
            ->with(['author', 'categories', 'comments', 'featuredImage'])
            ->where('is_visible', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new post.
     */
    public function createPost(array $data): Post
    {
        return $this->postRepository->create($data);
    }

    /**
     * Update an existing post.
     */
    public function updatePost(Post $post, array $data): Post
    {
        $this->postRepository->update($post->id, $data);

        return $post->fresh();
    }

    /**
     * Delete a post.
     */
    public function deletePost(Post $post): bool
    {
        return $this->postRepository->delete($post->id);
    }

    /**
     * Get featured image URL for a post.
     */
    public function getFeaturedImageUrl(Post $post): ?string
    {
        return $post->image ? Media::find($post->image)?->url : null;
    }

    /**
     * {@inheritDoc}
     */
    public function firstOrNew(array $attributes): Post
    {
        return $this->postRepository->firstOrNew($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail(int | string $id): Post
    {
        return $this->postRepository->findOrFail($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getTableQuery(): Builder
    {
        return $this->postRepository->query()
            ->with(['featuredImage'])
            ->where('post_type', 'blog');
    }
}
