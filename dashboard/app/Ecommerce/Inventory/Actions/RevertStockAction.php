<?php

namespace App\Ecommerce\Inventory\Actions;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RevertStockAction
{
    /**
     * Restore cancelled items back into their source facilities securely.
     *
     * @param int $productId
     * @param int $warehouseId
     * @param int $quantity
     * @return bool
     * @throws Exception
     */
    public function execute(int $productId, int $warehouseId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            // 1. Atomic increment on inventory stock
            DB::table('shop_product_inventory_stocks')
                ->where('shop_product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->increment('stock_quantity', $quantity);

            // 2. Optimistic Lock update on denormalized overall product stocks
            $product = Product::find($productId);

            if ($product) {
                $updated = DB::table('shop_products')
                    ->where('id', $productId)
                    ->where('version', $product->version)
                    ->update([
                        'total_stock' => $product->total_stock + $quantity,
                        'version' => $product->version + 1,
                    ]);

                if ($updated === 0) {
                    throw ValidationException::withMessages([
                        'order' => [__('messages.concurrency_collision')],
                    ]);
                }
            }

            return true;
        }, 3);
    }

    /**
     * Revert stock for an individual order item based on product type (Simple, Variable, Combo).
     *
     * @param OrderItem $item
     * @return void
     */
    public function revertOrderItem(OrderItem $item): void
    {
        $metadata = $item->metadata ?? [];
        $order = $item->order;
        $auditService = app(\App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface::class);

        // 1. Combo product restoration via component snapshot
        if (!empty($metadata['combo_components']) && is_array($metadata['combo_components'])) {
            foreach ($metadata['combo_components'] as $component) {
                $qtyToRestore = (int) ($component['total_qty'] ?? (($component['qty_per_combo'] ?? 1) * $item->qty));
                
                if (!empty($component['variant_id'])) {
                    $variant = ProductVariant::find($component['variant_id']);
                    if ($variant) {
                        $variant->increment('qty', $qtyToRestore);
                        $variant->product?->increment('total_stock', $qtyToRestore);
                        if ($order) {
                            $auditService->logStockRestoration($order, $variant, $qtyToRestore, 'Order cancelled - combo variant component restoration');
                        }
                    }
                } elseif (!empty($component['product_id'])) {
                    $product = Product::find($component['product_id']);
                    if ($product) {
                        $product->increment('qty', $qtyToRestore);
                        $product->increment('total_stock', $qtyToRestore);
                        if ($order) {
                            $auditService->logStockRestoration($order, $product, $qtyToRestore, 'Order cancelled - combo product component restoration');
                        }
                    }
                }
            }
            return;
        }

        // 2. Variable product restoration via variant_id
        if ($item->variant_id) {
            $variant = ProductVariant::find($item->variant_id);
            if ($variant) {
                $variant->increment('qty', $item->qty);
                $variant->product?->increment('total_stock', $item->qty);
                if ($order) {
                    $auditService->logStockRestoration($order, $variant, $item->qty, 'Order cancelled - variant stock restoration');
                }
                return;
            }
        }

        // 3. Simple product restoration
        if ($item->shop_product_id) {
            $product = Product::find($item->shop_product_id);
            if ($product) {
                $product->increment('qty', $item->qty);
                $product->increment('total_stock', $item->qty);
                if ($order) {
                    $auditService->logStockRestoration($order, $product, $item->qty, 'Order cancelled - simple product stock restoration');
                }
            }
        }
    }
}
