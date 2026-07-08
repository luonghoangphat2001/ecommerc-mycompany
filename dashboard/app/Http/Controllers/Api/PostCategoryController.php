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

/**
 * @group Blog
 *
 * APIs for managing blog post categories.
 */
class PostCategoryController extends Controller
{
    use ApiResponse;

    protected $postCategoryService;

    public function __construct(PostCategoryServiceInterface $postCategoryService)
    {
        $this->postCategoryService = $postCategoryService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $categories = $this->postCategoryService->paginate($perPage, ['*'], ['posts']);
        return $this->ok(PostCategoryResource::collection($categories));
    }

    public function store(StorePostCategoryRequest $request)
    {
        $dto = PostCategoryDTO::fromRequest($request);
        $category = $this->postCategoryService->createCategory($dto->toArray());
        
        return $this->created(new PostCategoryResource($category));
    }

    public function show(PostCategory $postCategory)
    {
        return $this->ok(new PostCategoryResource($postCategory->load('posts')));
    }

    public function update(UpdatePostCategoryRequest $request, PostCategory $postCategory)
    {
        $dto = PostCategoryDTO::fromRequest($request);
        $category = $this->postCategoryService->updateCategory($postCategory, $dto->toArray());
        
        return $this->ok(new PostCategoryResource($category));
    }

    public function destroy(PostCategory $postCategory)
    {
        $this->postCategoryService->deleteCategory($postCategory);
        return $this->noContent();
    }

    public function posts(PostCategory $postCategory, Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $posts = $this->postCategoryService->getPaginatedPostsByCategory($postCategory, $perPage);
        return $this->ok(PostResource::collection($posts));
    }
}
