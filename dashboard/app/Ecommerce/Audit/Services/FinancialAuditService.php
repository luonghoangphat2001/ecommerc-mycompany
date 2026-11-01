<?php

namespace App\Ecommerce\Audit\Services;

use App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Logging\ModuleLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class FinancialAuditService implements FinancialAuditServiceInterface
{
    /**
     * @inheritDoc
     */
    public function logProductPriceChange(
        Product $product,
        int|float $oldPrice,
        int|float $newPrice,
        int|float|null $oldSalePrice = null,
        int|float|null $newSalePrice = null,
        ?string $reason = null
    ): void {
        try {
            $user = Auth::user();
            $actorId = $user?->id ?? 'system';
            $actorRole = $user?->role ?? ($user ? 'admin' : 'system');

            $context = [
                'event' => 'product.price_changed',
                'entity_type' => Product::class,
                'entity_id' => $product->id,
                'sku' => $product->sku,
                'old_price' => (int) $oldPrice,
                'new_price' => (int) $newPrice,
                'old_sale_price' => $oldSalePrice !== null ? (int) $oldSalePrice : null,
                'new_sale_price' => $newSalePrice !== null ? (int) $newSalePrice : null,
                'currency' => 'VND',
                'actor_id' => $actorId,
                'actor_role' => $actorRole,
                'reason' => $reason,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => substr(request()->userAgent() ?? 'CLI/System', 0, 255),
            ];

            // 1. Spatie Activity Log (Database)
            if (function_exists('activity')) {
                $act = activity('financial')
                    ->performedOn($product)
                    ->event('product.price_changed')
                    ->withProperties($context);

                if ($user) {
                    $act->causedBy($user);
                }

                $act->log("Product [{$product->sku}] price adjusted from {$oldPrice} to {$newPrice} VND");
            }

            // 2. Structured File Logging (ModuleLogger)
            ModuleLogger::product()->info('product_price_changed', "Price modified for product #{$product->id} ({$product->sku})", $context);
        } catch (Throwable $e) {
            // Non-blocking: audit failure must not abort core transaction
            report($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function logVariantPriceChange(
        ProductVariant $variant,
        int|float $oldPrice,
        int|float $newPrice,
        int|float|null $oldSalePrice = null,
        int|float|null $newSalePrice = null,
        ?string $reason = null
    ): void {
        try {
            $user = Auth::user();
            $actorId = $user?->id ?? 'system';
            $actorRole = $user?->role ?? ($user ? 'admin' : 'system');

            $context = [
                'event' => 'variant.price_changed',
                'entity_type' => ProductVariant::class,
                'entity_id' => $variant->id,
                'product_id' => $variant->product_id,
                'variant_sku' => $variant->sku,
                'old_price' => (int) $oldPrice,
                'new_price' => (int) $newPrice,
                'old_sale_price' => $oldSalePrice !== null ? (int) $oldSalePrice : null,
                'new_sale_price' => $newSalePrice !== null ? (int) $newSalePrice : null,
                'currency' => 'VND',
                'actor_id' => $actorId,
                'actor_role' => $actorRole,
                'reason' => $reason,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => substr(request()->userAgent() ?? 'CLI/System', 0, 255),
            ];

            if (function_exists('activity')) {
                $act = activity('financial')
                    ->performedOn($variant)
                    ->event('variant.price_changed')
                    ->withProperties($context);

                if ($user) {
                    $act->causedBy($user);
                }

                $act->log("Variant [{$variant->sku}] price adjusted from {$oldPrice} to {$newPrice} VND");
            }

            ModuleLogger::product()->info('variant_price_changed', "Price modified for variant #{$variant->id} ({$variant->sku})", $context);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function logOrderFinalized(Order $order, array $totalsBreakdown = []): void
    {
        try {
            $user = Auth::user();
            $actorId = $order->user_id ?? ($user?->id ?? 'guest');

            $context = [
                'event' => 'order.finalized',
                'entity_type' => Order::class,
                'entity_id' => $order->id,
                'order_number' => $order->number,
                'subtotal' => (int) $order->subtotal,
                'shipping_total' => (int) ($order->shipping_total ?? 0),
                'tax_total' => (int) ($order->tax_total ?? 0),
                'discount_total' => (int) ($order->discount_total ?? 0),
                'grand_total' => (int) $order->total,
                'currency' => $order->currency ?? 'VND',
                'customer_id' => $order->user_id,
                'actor_id' => $actorId,
                'breakdown' => $totalsBreakdown,
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ];

            if (function_exists('activity')) {
                $act = activity('financial')
                    ->performedOn($order)
                    ->event('order.finalized')
                    ->withProperties($context);

                if ($user) {
                    $act->causedBy($user);
                }

                $act->log("Order #{$order->number} finalized with total {$order->total} VND");
            }

            ModuleLogger::order()->info('order_finalized', "Order #{$order->number} finalized", $context);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function logStockRestoration(Order $order, Model $item, int $restoredQty, string $reason): void
    {
        try {
            $user = Auth::user();
            $context = [
                'event' => 'stock.restored',
                'order_id' => $order->id,
                'order_number' => $order->number,
                'item_type' => get_class($item),
                'item_id' => $item->id,
                'sku' => $item->sku ?? null,
                'restored_quantity' => $restoredQty,
                'reason' => $reason,
                'actor_id' => $user?->id ?? 'system',
            ];

            if (function_exists('activity')) {
                $act = activity('financial')
                    ->performedOn($item)
                    ->event('stock.restored')
                    ->withProperties($context);

                if ($user) {
                    $act->causedBy($user);
                }

                $act->log("Restored {$restoredQty} units to item #{$item->id} due to order #{$order->number} cancellation ({$reason})");
            }

            ModuleLogger::order()->info('stock_restored', "Restored {$restoredQty} units for item #{$item->id}", $context);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
