<?php

namespace App\Http\Controllers\Api;

use App\Ecommerce\Product\Contracts\ProductServiceInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\ProductResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OAT;
use App\Swagger\Attributes\ApiGet;
use App\Swagger\Attributes\ApiList;

class ProductController extends BaseApiController
{

    public function __construct(
        protected ProductServiceInterface $productService
    ) {}

    #[ApiList(
        path: '/products',
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
            perPage: (int) $request->get('per_page', 15),
            relations: ['categories', 'brand', 'featuredImage', 'inventories'],
            filters: $request->all()
        );
        
        return $this->ok(ProductResource::collection($products));
    }

    #[ApiGet(
        path: '/products/by-slug/{slug}',
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
            return $this->notFound(__('messages.product_not_found') ?? 'Product not found');
        }
        
        return $this->ok(new ProductResource($product));
    }

    #[ApiGet(
        path: '/products/{product}',
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
        $relations = ['categories', 'brand', 'featuredImage', 'comments', 'inventories'];

        if (is_numeric($id)) {
            $product = $this->productService->findOrFail($id, ['*'], $relations);
        } else {
            $product = $this->productService->findBySlug((string) $id, $relations);
            if (!$product) {
                return $this->notFound(__('messages.product_not_found') ?? 'Product not found');
            }
        }

        return $this->ok(new ProductResource($product));
    }

    public function related(Request $request, $productId)
    {
        return $this->getRelatedOrRecommendedProducts($request, $productId);
    }

    public function recommendations(Request $request, $productId)
    {
        return $this->getRelatedOrRecommendedProducts($request, $productId);
    }

    protected function getRelatedOrRecommendedProducts(Request $request, $productId)
    {
        $limit = (int) $request->get('limit', 4);
        $relations = ['categories', 'brand', 'featuredImage', 'inventories'];

        $product = is_numeric($productId)
            ? \App\Models\Product::with('categories')->find($productId)
            : \App\Models\Product::with('categories')->where('slug', (string) $productId)->first();

        $query = \App\Models\Product::with($relations)->where('is_visible', true);

        if ($product) {
            $query->where('id', '!=', $product->id);
            $categoryIds = $product->categories->pluck('id')->all();

            if (!empty($categoryIds) || $product->shop_brand_id) {
                $query->where(function ($q) use ($categoryIds, $product) {
                    if (!empty($categoryIds)) {
                        $q->whereHas('categories', fn ($cq) => $cq->whereIn('shop_categories.id', $categoryIds));
                    }
                    if ($product->shop_brand_id) {
                        $q->orWhere('shop_brand_id', $product->shop_brand_id);
                    }
                });
            }
        }

        $items = $query->orderByDesc('featured')->orderByDesc('id')->limit($limit)->get();

        if ($items->count() < $limit) {
            $excludeIds = $items->pluck('id')->all();
            if ($product) {
                $excludeIds[] = $product->id;
            }
            $fallback = \App\Models\Product::with($relations)
                ->where('is_visible', true)
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('featured')
                ->orderByDesc('id')
                ->limit($limit - $items->count())
                ->get();
            $items = $items->concat($fallback);
        }

        return $this->ok(ProductResource::collection($items));
    }
}
