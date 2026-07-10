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
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class PostController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PostServiceInterface $postService
    ) {}

    #[ApiList(
        path: '/posts',
        summary: 'List of Posts',
        tags: 'Storefront - Posts',
        requiresAuth: false
    )]
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $posts = $this->postService->getPaginatedPosts($perPage);
        
        return $this->ok(PostResource::collection($posts));
    }

    #[ApiPost(
        path: '/posts',
        summary: 'Create Post',
        tags: 'Storefront - Posts',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['title', 'slug', 'content'],
                properties: [
                    new OAT\Property(property: 'title', type: 'string', example: 'New Post'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'new-post'),
                    new OAT\Property(property: 'content', type: 'string', example: 'Content here'),
                    new OAT\Property(property: 'status', type: 'string', example: 'published'),
                ]
            )
        )
    )]
    public function store(StorePostRequest $request)
    {
        $dto = PostDTO::fromRequest($request);
        $post = $this->postService->createPost($dto->toArray());
        
        return $this->created(new PostResource($post));
    }

    #[ApiGet(
        path: '/posts/{post}',
        summary: 'Post Details',
        tags: 'Storefront - Posts',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'post', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Post ID')
        ]
    )]
    public function show(Post $post)
    {
        return $this->ok(new PostResource($post->load(['author', 'categories', 'comments', 'featuredImage'])));
    }

    #[ApiUpdate(
        path: '/posts/{post}',
        summary: 'Update Post',
        tags: 'Storefront - Posts',
        parameters: [
            new OAT\Parameter(name: 'post', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Post ID')
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'title', type: 'string', example: 'Updated Post'),
                    new OAT\Property(property: 'content', type: 'string', example: 'Updated Content'),
                ]
            )
        )
    )]
    public function update(UpdatePostRequest $request, Post $post)
    {
        $dto = PostDTO::fromRequest($request);
        $updatedPost = $this->postService->updatePost($post, $dto->toArray());
        
        return $this->ok(new PostResource($updatedPost));
    }

    #[ApiDelete(
        path: '/posts/{post}',
        summary: 'Delete Post',
        tags: 'Storefront - Posts',
        parameters: [
            new OAT\Parameter(name: 'post', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Post ID')
        ]
    )]
    public function destroy(Post $post)
    {
        $this->postService->deletePost($post);
        return $this->noContent();
    }
}
