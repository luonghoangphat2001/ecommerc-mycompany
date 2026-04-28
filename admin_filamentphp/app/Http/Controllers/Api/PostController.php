<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Post\StorePostRequest;
use App\Http\Requests\API\Post\UpdatePostRequest;
use App\Http\Resources\Api\PostResource;
use App\Models\Post;
use App\Ecommerce\Post\Contracts\PostServiceInterface;
use App\Ecommerce\Post\DTOs\Post\PostDTO;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @group Blog
 *
 * APIs for managing blog posts.
 */
class PostController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PostServiceInterface $postService
    ) {}

    /**
     * List Blog Posts
     * 
     * Retrieve a paginated list of blog posts.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $posts = $this->postService->getPaginatedPosts($perPage);
        
        return $this->ok(PostResource::collection($posts));
    }

    /**
     * Create Blog Post
     * 
     * Create a new blog post with the provided data.
     */
    public function store(StorePostRequest $request)
    {
        $dto = PostDTO::fromRequest($request);
        $post = $this->postService->createPost($dto->toArray());
        
        return $this->created(new PostResource($post));
    }

    /**
     * Get Blog Post
     * 
     * Retrieve detailed information about a specific blog post.
     */
    public function show(Post $post)
    {
        return $this->ok(new PostResource($post->load(['author', 'categories', 'comments'])));
    }

    /**
     * Update Blog Post
     * 
     * Update an existing blog post.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $dto = PostDTO::fromRequest($request);
        $updatedPost = $this->postService->updatePost($post, $dto->toArray());
        
        return $this->ok(new PostResource($updatedPost));
    }

    /**
     * Delete Blog Post
     * 
     * Remove a specific blog post.
     */
    public function destroy(Post $post)
    {
        $this->postService->deletePost($post);
        return $this->noContent();
    }
}
