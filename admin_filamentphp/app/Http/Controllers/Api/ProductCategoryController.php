<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ProductCategory\StoreProductCategoryRequest;
use App\Http\Requests\API\ProductCategory\UpdateProductCategoryRequest;
use App\Http\Resources\Api\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Contracts\Services\ProductCategoryServiceInterface;
use App\DTOs\ProductCategory\ProductCategoryDTO;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @group Shop
 *
 * APIs for managing product categories.
 */
class ProductCategoryController extends Controller
{
    use ApiResponse;

    protected $categoryService;

    public function __construct(ProductCategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $categories = $this->categoryService->paginate($perPage, ['*'], ['products']);
        
        return $this->ok(ProductCategoryResource::collection($categories));
    }

    public function store(StoreProductCategoryRequest $request)
    {
        $dto = ProductCategoryDTO::fromRequest($request);
        $category = $this->categoryService->createCategory($dto->toArray());
        
        return $this->created(new ProductCategoryResource($category));
    }

    public function show(ProductCategory $productCategory)
    {
        return $this->ok(new ProductCategoryResource($productCategory->load('products')));
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $dto = ProductCategoryDTO::fromRequest($request);
        $category = $this->categoryService->updateCategory($productCategory, $dto->toArray());
        
        return $this->ok(new ProductCategoryResource($category));
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->categoryService->deleteCategory($productCategory);
        return $this->noContent();
    }
}
