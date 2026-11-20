<?php

namespace App\Ecommerce\Inventory\Actions;

use App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface;
use App\Ecommerce\Inventory\Concerns\AdjustsProductStockAtomically;
use App\Ecommerce\Inventory\Contracts\InventoryRepositoryInterface;
use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Models\OrderItem;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RevertStockAction
{
    use AdjustsProductStockAtomically;

    public function __construct(
        protected FinancialAuditServiceInterface $auditService,
        protected ProductRepositoryInterface $productRepository,
        protected InventoryRepositoryInterface $inventoryRepository,
    ) {}

    /**
     * Restore cancelled items back into their source facilities securely.
     *
     * @throws Exception
     */
    public function execute(int $productId, int $warehouseId, int $quantity, ?int $variantId = null): bool
    {
        return $this->adjustProductStockAtomically($productId, $warehouseId, $quantity, false, $variantId);
    }

    /**
     * Revert stock for an individual order item based on product type (Simple, Variable, Combo).
     */
    public function revertOrderItem(OrderItem $item): void
    {
        $metadata = $item->metadata ?? [];
        $order = $item->order;
        // 1. Combo product restoration via component snapshot
        if (! empty($metadata['combo_components']) && is_array($metadata['combo_components'])) {
            foreach ($metadata['combo_components'] as $component) {
                $qtyToRestore = (int) ($component['total_qty'] ?? (($component['qty_per_combo'] ?? 1) * $item->qty));
                $source = $component['stock_source'] ?? null;

                if (is_array($source) && ! empty($source['inventory_id']) && ! empty($component['product_id'])) {
                    $this->restoreFromSource($item, $source, (int) $component['product_id'], $qtyToRestore, $component['variant_id'] ?? null);

                    continue;
                }

                if (! empty($component['variant_id'])) {
                    $variant = $this->productRepository->findVariantById((int) $component['variant_id']);
                    if ($variant) {
                        $this->restoreVariantStock($variant, $qtyToRestore, $order, 'Order cancelled - combo variant component restoration');
                    }
                } elseif (! empty($component['product_id'])) {
                    $product = $this->productRepository->find((int) $component['product_id']);
                    if ($product) {
                        $this->restoreProductStock($product, $qtyToRestore, $order, 'Order cancelled - combo product component restoration');
                    }
                }
            }

            return;
        }

        // 2. Variable product restoration via variant_id
        if ($item->variant_id) {
            if (! empty($metadata['stock_source']) && is_array($metadata['stock_source'])) {
                $this->restoreFromSource($item, $metadata['stock_source'], (int) $item->shop_product_id, (int) $item->qty, (int) $item->variant_id);

                return;
            }

            $variant = $this->productRepository->findVariantById((int) $item->variant_id);
            if ($variant) {
                $this->restoreVariantStock($variant, $item->qty, $order, 'Order cancelled - variant stock restoration');

                return;
            }
        }

        // 3. Simple product restoration
        if ($item->shop_product_id) {
            if (! empty($metadata['inventory_id'])) {
                $this->restoreFromSource($item, $metadata, (int) $item->shop_product_id, (int) $item->qty, $item->variant_id);

                return;
            }

            $product = $this->productRepository->find((int) $item->shop_product_id);
            if ($product) {
                $this->restoreProductStock($product, $item->qty, $order, 'Order cancelled - simple product stock restoration');
            }
        }
    }

    public function restoreOrderItemQuantity(OrderItem $item, int $quantity): int
    {
        if ($quantity <= 0) {
            return 0;
        }

        $remaining = $quantity;
        $deductions = DB::table('shop_inventory_movements')
            ->where('source_type', $item->getMorphClass())
            ->where('source_id', $item->id)
            ->where('quantity_changed', '<', 0)
            ->orderBy('shop_inventory_movements.id')
            ->get();

        foreach ($deductions as $deduction) {
            if ($remaining <= 0) {
                break;
            }

            $restored = $this->restoreFromSource(
                $item,
                ['inventory_id' => $deduction->warehouse_id],
                (int) $deduction->shop_product_id,
                $remaining,
                $deduction->variant_id ? (int) $deduction->variant_id : null,
                (int) $deduction->id,
            );
            $remaining -= $restored;
        }

        return $quantity - $remaining;
    }

    private function restoreFromSource(OrderItem $item, array $source, int $productId, int $quantity, ?int $variantId, ?int $deductionId = null): int
    {
        $inventoryId = (int) $source['inventory_id'];

        return DB::transaction(function () use ($item, $productId, $quantity, $variantId, $inventoryId, $deductionId): int {
            $deduction = DB::table('shop_inventory_movements')
                ->where('source_type', $item->getMorphClass())
                ->where('source_id', $item->id)
                ->where('warehouse_id', $inventoryId)
                ->when($variantId === null, fn ($query) => $query->whereNull('variant_id'))
                ->when($variantId !== null, fn ($query) => $query->where('variant_id', $variantId))
                ->where('quantity_changed', '<', 0)
                ->when($deductionId !== null, fn ($query) => $query->where('id', $deductionId))
                ->latest('shop_inventory_movements.id')
                ->first();

            if (! $deduction) {
                return 0;
            }

            $alreadyRestored = (int) DB::table('shop_inventory_movements')
                ->where('restoration_of_movement_id', $deduction->id)
                ->sum('quantity_changed');
            $quantity = min($quantity, max(0, abs((int) $deduction->quantity_changed) - $alreadyRestored));
            if ($quantity <= 0) {
                return 0;
            }

            $before = $this->inventoryRepository->getStock($productId, $inventoryId);
            $this->execute($productId, $inventoryId, $quantity, $variantId);
            $after = $this->inventoryRepository->getStock($productId, $inventoryId);
            $this->inventoryRepository->recordMovement([
                'shop_product_id' => $productId,
                'warehouse_id' => $inventoryId,
                'variant_id' => $variantId,
                'movement_type' => 'order_stock_restoration',
                'source_type' => OrderItem::class,
                'source_id' => $item->id,
                'restoration_of_movement_id' => $deduction->id,
                'prev_stock' => $before,
                'quantity_changed' => $quantity,
                'new_stock' => $after,
            ]);

            return $quantity;
        }, 3);
    }

    protected function restoreVariantStock(Model $variant, int $quantity, mixed $order, string $reason): void
    {
        $this->restoreStock($variant, $quantity, $order, $reason, $variant->product);
    }

    protected function restoreProductStock(Model $product, int $quantity, mixed $order, string $reason): void
    {
        $this->restoreStock($product, $quantity, $order, $reason);
    }

    protected function restoreStock(Model $item, int $quantity, mixed $order, string $reason, ?Model $stockParent = null): void
    {
        $item->increment('qty', $quantity);
        ($stockParent ?? $item)->increment('total_stock', $quantity);

        if ($order) {
            $this->auditService->logStockRestoration($order, $item, $quantity, $reason);
        }
    }
}
