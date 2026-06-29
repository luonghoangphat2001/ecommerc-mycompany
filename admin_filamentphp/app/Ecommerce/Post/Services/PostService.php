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
     * @return LengthAwarePaginator
     */
    public function getPaginatedPosts(int $perPage = 10): LengthAwarePaginator
    {
        return $this->postRepository->paginate($perPage, ['*'], ['author', 'categories', 'comments', 'featuredImage']);
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
