<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\ProductServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

/**
 * @group Shop
 *
 * APIs for managing products (Tours, Rooms, etc.)
 */
class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProductServiceInterface $productService
    ) {}

    /**
     * Get list of products.
     *
     * @queryParam category_id int Filter by category ID. Example: 1
     * @queryParam brand_id int Filter by brand ID. Example: 2
     * @queryParam type string Filter by product type (tour, room). Example: tour
     * @queryParam search string Search by product name. Example: Beach
     */
    public function index(Request $request)
    {
        $products = $this->productService->paginate(
            perPage: $request->get('per_page', 15),
            relations: ['categories', 'brand', 'featuredImage']
        );
        
        return $this->ok(ProductResource::collection($products));
    }

    /**
     * Get product details.
     *
     * @urlParam id int required The ID of the product. Example: 1
     */
    public function show($id)
    {
        $product = $this->productService->findOrFail(
            $id,
            relations: ['categories', 'brand', 'featuredImage', 'comments']
        );

        return $this->ok(new ProductResource($product));
    }
}
