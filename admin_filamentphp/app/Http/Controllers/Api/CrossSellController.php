<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CrossSellResource;
use App\Ecommerce\CrossSell\Contracts\CrossSellServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group Marketing
 *
 * APIs for managing cross-sell products
 */
class CrossSellController extends Controller
{
    use ApiResponse;

    protected CrossSellServiceInterface $crossSellService;

    public function __construct(CrossSellServiceInterface $crossSellService)
    {
        $this->crossSellService = $crossSellService;
    }

    /**
     * Get cross-sell products for a product.
     *
     * Returns a list of cross-sell product relationships for the given product ID.
     *
     * @urlParam productId integer required The ID of the product. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "sort_order": 0,
     *       "is_active": true,
     *       "product": {
     *         "id": 3,
     *         "name": "Related Product",
     *         "price": 300000,
     *         "stock": 5
     *       }
     *     }
     *   ]
     * }
     * @response 404 {
     *   "message": "Product not found."
     * }
     */
    public function index(int $productId): JsonResponse
    {
        $crossSells = $this->crossSellService->getCrossSellsForProduct($productId);

        return $this->ok(CrossSellResource::collection($crossSells));
    }
}
