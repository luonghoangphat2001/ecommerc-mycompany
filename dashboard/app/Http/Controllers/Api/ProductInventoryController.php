<?php

namespace App\Http\Controllers\Api;

use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\InventoryResource;
use Illuminate\Http\Request;

class ProductInventoryController extends BaseApiController
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get inventory for a product across all warehouses
     */
    public function index(Request $request, $productId)
    {
        try {
            $product = $this->productRepository->find((int)$productId, ['*'], ['inventories']);
            
            if (!$product) {
                return $this->notFound(__('messages.product_not_found') ?? 'Product not found');
            }
            
            $variantId = $request->get('variant_id');
            $inventories = $product->inventories;
            
            if ($variantId) {
                $inventories = $inventories->where('pivot.variant_id', $variantId);
            }
            
            return $this->ok(InventoryResource::collection($inventories));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 404);
        }
    }
}
