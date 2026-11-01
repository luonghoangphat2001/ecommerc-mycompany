<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\API\PostRequest;
use App\Http\Resources\Api\PostResource;
use App\Models\Post;
use App\Ecommerce\Post\Contracts\PostServiceInterface;
use App\Ecommerce\Post\DTOs\Post\PostDTO;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class PostController extends BaseApiController
{

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
        $perPage = (int) $request->get('per_page', 10);
        $posts = $this->postService->getPaginatedPosts($perPage, $request->all());
        
        return $this->ok(PostResource::collection($posts));
    }

    public function featured(Request $request)
    {
        $limit = (int) $request->get('limit', 4);
        $posts = Post::with(['author', 'categories', 'comments', 'featuredImage'])
            ->where('is_visible', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

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
    public function store(PostRequest $request)
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
    public function show($post)
    {
        $relations = ['author', 'categories', 'comments', 'featuredImage'];
        $model = is_numeric($post)
            ? Post::with($relations)->find($post)
            : Post::with($relations)->where('slug', (string) $post)->first();

        if (!$model) {
            return $this->notFound('Post not found');
        }

        return $this->ok(new PostResource($model));
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
    public function update(PostRequest $request, Post $post)
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
