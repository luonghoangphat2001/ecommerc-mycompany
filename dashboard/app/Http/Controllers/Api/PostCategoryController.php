<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\PostCategory\StorePostCategoryRequest;
use App\Http\Requests\API\PostCategory\UpdatePostCategoryRequest;
use App\Http\Resources\Api\PostCategoryResource;
use App\Http\Resources\Api\PostResource;
use App\Models\PostCategory;
use App\Ecommerce\Post\Contracts\PostCategoryServiceInterface;
use App\Ecommerce\Post\DTOs\PostCategory\PostCategoryDTO;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class PostCategoryController extends Controller
{
    use ApiResponse;

    protected $postCategoryService;

    public function __construct(PostCategoryServiceInterface $postCategoryService)
    {
        $this->postCategoryService = $postCategoryService;
    }

    #[ApiList(
        path: '/post-categories',
        summary: 'List of Post Categories',
        tags: 'Storefront - Post Categories',
        requiresAuth: false
    )]
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $categories = $this->postCategoryService->paginate($perPage, ['*'], ['posts']);
        return $this->ok(PostCategoryResource::collection($categories));
    }

    #[ApiPost(
        path: '/post-categories',
        summary: 'Create Post Category',
        tags: 'Storefront - Post Categories',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['name', 'slug'],
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'News'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'news'),
                    new OAT\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        )
    )]
    public function store(StorePostCategoryRequest $request)
    {
        $dto = PostCategoryDTO::fromRequest($request);
        $category = $this->postCategoryService->createCategory($dto->toArray());
        
        return $this->created(new PostCategoryResource($category));
    }

    #[ApiGet(
        path: '/post-categories/{postCategory}',
        summary: 'Post Category Details',
        tags: 'Storefront - Post Categories',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'postCategory', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Category ID')
        ]
    )]
    public function show(PostCategory $postCategory)
    {
        return $this->ok(new PostCategoryResource($postCategory->load('posts')));
    }

    #[ApiUpdate(
        path: '/post-categories/{postCategory}',
        summary: 'Update Post Category',
        tags: 'Storefront - Post Categories',
        parameters: [
            new OAT\Parameter(name: 'postCategory', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Category ID')
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'News Updated'),
                ]
            )
        )
    )]
    public function update(UpdatePostCategoryRequest $request, PostCategory $postCategory)
    {
        $dto = PostCategoryDTO::fromRequest($request);
        $category = $this->postCategoryService->updateCategory($postCategory, $dto->toArray());
        
        return $this->ok(new PostCategoryResource($category));
    }

    #[ApiDelete(
        path: '/post-categories/{postCategory}',
        summary: 'Delete Post Category',
        tags: 'Storefront - Post Categories',
        parameters: [
            new OAT\Parameter(name: 'postCategory', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Category ID')
        ]
    )]
    public function destroy(PostCategory $postCategory)
    {
        $this->postCategoryService->deleteCategory($postCategory);
        return $this->noContent();
    }

    #[ApiList(
        path: '/post-categories/{postCategory}/posts',
        summary: 'List of Posts in Category',
        tags: 'Storefront - Post Categories',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'postCategory', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Category ID')
        ]
    )]
    public function posts(PostCategory $postCategory, Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $posts = $this->postCategoryService->getPaginatedPostsByCategory($postCategory, $perPage);
        return $this->ok(PostResource::collection($posts));
    }
}
