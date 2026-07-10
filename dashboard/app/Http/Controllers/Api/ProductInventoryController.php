<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductInventoryController extends Controller
{
    /**
     * Get inventory for a product across all warehouses
     */
    public function index(Request $request, $productId)
    {
        try {
            $product = Product::with('inventories')->findOrFail($productId);
            
            $variantId = $request->get('variant_id');
            
            $inventories = $product->inventories;
            
            if ($variantId) {
                $inventories = $inventories->where('pivot.variant_id', $variantId);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $inventories->map(function ($inventory) {
                    return [
                        'warehouse_id' => $inventory->id,
                        'warehouse_name' => $inventory->name ?? 'Unknown Warehouse',
                        'warehouse_location' => $inventory->location ?? '',
                        'quantity' => $inventory->pivot->stock_quantity ?? 0,
                        'variant_id' => $inventory->pivot->variant_id ?? null,
                    ];
                })->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 404);
        }
    }
}
