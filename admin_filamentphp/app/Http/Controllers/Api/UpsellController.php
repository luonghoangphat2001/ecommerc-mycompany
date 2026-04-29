<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UpsellResource;
use App\Models\Product;
use App\Settings\MarketingSettings;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group Marketing
 *
 * APIs for managing upsell products
 */
class UpsellController extends Controller
{
    use ApiResponse;

    /**
     * Get upsell products for a product.
     *
     * Returns a list of upsell product relationships for the given product ID.
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
     *         "id": 2,
     *         "name": "Premium Product",
     *         "price": 500000,
     *         "stock": 10
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
        if (!app(MarketingSettings::class)->upsell_enabled) {
            return $this->ok([]);
        }

        $product = Product::find($productId);

        if (!$product) {
            return $this->notFound(__('messages.api.product_not_found'));
        }

        $upsells = $product->upsells()
            ->where('is_active', true)
            ->with('upsellProduct')
            ->orderBy('sort_order')
            ->get();

        return $this->ok(UpsellResource::collection($upsells));
    }
}
