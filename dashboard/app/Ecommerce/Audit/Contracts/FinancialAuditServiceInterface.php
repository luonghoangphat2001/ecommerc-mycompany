<?php

namespace App\Ecommerce\Audit\Contracts;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

interface FinancialAuditServiceInterface
{
    /**
     * Log a product regular or sale price mutation.
     */
    public function logProductPriceChange(
        Product $product,
        int|float $oldPrice,
        int|float $newPrice,
        int|float|null $oldSalePrice = null,
        int|float|null $newSalePrice = null,
        ?string $reason = null
    ): void;

    /**
     * Log a variant regular or sale price mutation.
     */
    public function logVariantPriceChange(
        ProductVariant $variant,
        int|float $oldPrice,
        int|float $newPrice,
        int|float|null $oldSalePrice = null,
        int|float|null $newSalePrice = null,
        ?string $reason = null
    ): void;

    /**
     * Log an order finalized with its financial breakdown.
     */
    public function logOrderFinalized(Order $order, array $totalsBreakdown = []): void;

    /**
     * Log inventory stock restoration resulting from cancellation or return.
     */
    public function logStockRestoration(Order $order, Model $item, int $restoredQty, string $reason): void;

    /**
     * Log changes to financial, pricing, tax, shipping, discount, or payment settings.
     */
    public function logSettingChange(
        string $group,
        string $key,
        mixed $oldValue,
        mixed $newValue,
        ?string $reason = null
    ): void;
}
