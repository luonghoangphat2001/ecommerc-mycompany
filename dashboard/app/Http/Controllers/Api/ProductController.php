<?php

namespace App\Http\Controllers\Api;

use App\Ecommerce\Product\Contracts\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProductServiceInterface $productService
    ) {}

    #[ApiList(
        path: '/api/storefront/v1/products',
        summary: 'List of Products',
        tags: 'Storefront - Products',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'category_id', in: 'query', required: false, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Filter by Category ID'),
            new OAT\Parameter(name: 'brand_id', in: 'query', required: false, schema: new OAT\Schema(type: 'integer', example: 2), description: 'Filter by Brand ID'),
            new OAT\Parameter(name: 'type', in: 'query', required: false, schema: new OAT\Schema(type: 'string', example: 'tour'), description: 'Filter by Product Type'),
            new OAT\Parameter(name: 'search', in: 'query', required: false, schema: new OAT\Schema(type: 'string', example: 'Beach'), description: 'Search by Name')
        ],
        responseData: '#/components/schemas/ProductResource'
    )]
    public function index(Request $request)
    {
        $products = $this->productService->paginate(
            perPage: $request->get('per_page', 15),
            relations: ['categories', 'brand', 'featuredImage', 'inventories']
        );
        
        return $this->ok(ProductResource::collection($products));
    }

    #[ApiGet(
        path: '/api/storefront/v1/products/by-slug/{slug}',
        summary: 'Product Details (by Slug)',
        tags: 'Storefront - Products',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'slug', in: 'path', required: true, schema: new OAT\Schema(type: 'string', example: 'san-pham-1'), description: 'Product Slug')
        ],
        responseData: '#/components/schemas/ProductResource'
    )]
    public function showBySlug($slug)
    {
        $product = $this->productService->findBySlug(
            $slug,
            ['categories', 'brand', 'featuredImage', 'comments', 'inventories']
        );
        
        if (!$product) {
            return $this->error('Product not found', 404);
        }
        
        return $this->ok(new ProductResource($product));
    }

    #[ApiGet(
        path: '/api/storefront/v1/products/{product}',
        summary: 'Product Details (by ID)',
        tags: 'Storefront - Products',
        requiresAuth: false,
        parameters: [
            new OAT\Parameter(name: 'product', in: 'path', required: true, schema: new OAT\Schema(type: 'integer', example: 1), description: 'Product ID')
        ],
        responseData: '#/components/schemas/ProductResource'
    )]
    public function show($id)
    {
        $product = $this->productService->findOrFail(
            $id,
            ['categories', 'brand', 'featuredImage', 'comments', 'inventories']
        );

        return $this->ok(new ProductResource($product));
    }
}
