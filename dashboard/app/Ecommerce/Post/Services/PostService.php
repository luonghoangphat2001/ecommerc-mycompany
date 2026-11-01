<?php

namespace App\Ecommerce\Post\Services;

use App\Ecommerce\Post\Contracts\PostRepositoryInterface;
use App\Ecommerce\Post\Contracts\PostServiceInterface;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService implements PostServiceInterface
{
    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * PostService constructor.
     *
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * Get all posts with relations.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedPosts(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = $this->postRepository->query()
            ->with(['author', 'categories', 'comments', 'featuredImage']);

        if (!array_key_exists('is_visible', $filters)) {
            $query->where('is_visible', true);
        } elseif ($filters['is_visible'] !== 'all' && $filters['is_visible'] !== null && $filters['is_visible'] !== '') {
            $query->where('is_visible', filter_var($filters['is_visible'], FILTER_VALIDATE_BOOLEAN));
        }

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? $filters['keyword'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $categoryFilter = $filters['category_id'] ?? $filters['category'] ?? null;
        if ($categoryFilter !== null && $categoryFilter !== '' && $categoryFilter !== 'all') {
            $query->whereHas('categories', function ($q) use ($categoryFilter) {
                if (is_numeric($categoryFilter)) {
                    $q->where('categories.id', (int) $categoryFilter);
                } else {
                    $q->where('categories.slug', (string) $categoryFilter);
                }
            });
        }

        return $query->orderByDesc('published_at')->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Create a new post.
     *
     * @param array $data
     * @return Post
     */
    public function createPost(array $data): Post
    {
        return $this->postRepository->create($data);
    }

    /**
     * Update an existing post.
     *
     * @param Post $post
     * @param array $data
     * @return Post
     */
    public function updatePost(Post $post, array $data): Post
    {
        $this->postRepository->update($post->id, $data);
        return $post->fresh();
    }

    /**
     * Delete a post.
     *
     * @param Post $post
     * @return bool
     */
    public function deletePost(Post $post): bool
    {
        return $this->postRepository->delete($post->id);
    }

    /**
     * Get featured image URL for a post.
     *
     * @param Post $post
     * @return string|null
     */
    public function getFeaturedImageUrl(Post $post): ?string
    {
        return $post->image ? \Awcodes\Curator\Models\Media::find($post->image)?->url : null;
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew(array $attributes): Post
    {
        return $this->postRepository->firstOrNew($attributes);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int|string $id): Post
    {
        return $this->postRepository->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->postRepository->query()
            ->with(['featuredImage'])
            ->where('post_type', 'blog');
    }
}
