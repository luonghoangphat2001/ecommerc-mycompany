<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\API\ProductCategoryRequest;
use App\Http\Resources\Api\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Ecommerce\Product\Contracts\ProductCategoryServiceInterface;
use App\Ecommerce\Product\DTOs\ProductCategory\ProductCategoryDTO;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;
use App\Swagger\Attributes\ApiPost;
use App\Swagger\Attributes\ApiUpdate;
use App\Swagger\Attributes\ApiDelete;

class ProductCategoryController extends BaseApiController
{

    protected $categoryService;

    public function __construct(ProductCategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    #[ApiList(
        path: '/product-categories',
        summary: 'List of Product Categories',
        tags: 'Storefront - Product Categories',
        requiresAuth: false,
        responseData: '#/components/schemas/ProductCategoryResource'
    )]
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 50);
        $categories = ProductCategory::with(['children'])
            ->withCount('products')
            ->paginate($perPage);
        
        return $this->ok(ProductCategoryResource::collection($categories));
    }

    #[ApiPost(
        path: '/product-categories',
        summary: 'Create Product Category',
        tags: 'Storefront - Product Categories',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                required: ['name', 'slug'],
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'Electronics'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'electronics'),
                    new OAT\Property(property: 'description', type: 'string', nullable: true, example: 'Electronics category'),
                    new OAT\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        responseData: '#/components/schemas/ProductCategoryResource'
    )]
    public function store(ProductCategoryRequest $request)
    {
        $dto = ProductCategoryDTO::fromRequest($request);
        $category = $this->categoryService->createCategory($dto->toArray());
        
        return $this->created(new ProductCategoryResource($category));
    }

    #[ApiGet(
        path: '/product-categories/{productCategory}',
        summary: 'Product Category Details',
        tags: 'Storefront - Product Categories',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'productCategory', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Category ID')
        ],
        responseData: '#/components/schemas/ProductCategoryResource'
    )]
    public function show($productCategory)
    {
        $category = is_numeric($productCategory)
            ? ProductCategory::with(['children'])->withCount('products')->findOrFail($productCategory)
            : ProductCategory::with(['children'])->withCount('products')->where('slug', (string) $productCategory)->firstOrFail();

        return $this->ok(new ProductCategoryResource($category));
    }

    public function products(Request $request, $category)
    {
        $categoryModel = is_numeric($category)
            ? ProductCategory::find($category)
            : ProductCategory::where('slug', (string) $category)->first();

        if (!$categoryModel) {
            return $this->notFound('Category not found');
        }

        $filters = array_merge($request->all(), ['category_id' => $categoryModel->id]);
        $productService = app(\App\Ecommerce\Product\Contracts\ProductServiceInterface::class);
        $products = $productService->paginate(
            perPage: (int) $request->get('per_page', 15),
            relations: ['categories', 'brand', 'featuredImage', 'inventories'],
            filters: $filters
        );

        return $this->ok(\App\Http\Resources\Api\ProductResource::collection($products));
    }

    #[ApiUpdate(
        path: '/product-categories/{productCategory}',
        summary: 'Update Product Category',
        tags: 'Storefront - Product Categories',
        parameters: [
            new OAT\Parameter(name: 'productCategory', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Category ID')
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                properties: [
                    new OAT\Property(property: 'name', type: 'string', example: 'Electronics Updated'),
                    new OAT\Property(property: 'slug', type: 'string', example: 'electronics-updated'),
                    new OAT\Property(property: 'description', type: 'string', nullable: true, example: 'Electronics category'),
                    new OAT\Property(property: 'is_active', type: 'boolean', example: true),
                ]
            )
        ),
        responseData: '#/components/schemas/ProductCategoryResource'
    )]
    public function update(ProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $dto = ProductCategoryDTO::fromRequest($request);
        $category = $this->categoryService->updateCategory($productCategory, $dto->toArray());
        
        return $this->ok(new ProductCategoryResource($category));
    }

    #[ApiDelete(
        path: '/product-categories/{productCategory}',
        summary: 'Delete Product Category',
        tags: 'Storefront - Product Categories',
        parameters: [
            new OAT\Parameter(name: 'productCategory', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Category ID')
        ]
    )]
    public function destroy(ProductCategory $productCategory)
    {
        $this->categoryService->deleteCategory($productCategory);
        return $this->noContent();
    }
}
