<?php

namespace App\Ecommerce\Checkout\Services\Pipes;

use App\Ecommerce\Checkout\DTOs\CheckoutResultDTO;
use App\Ecommerce\Checkout\DTOs\CheckoutRequestDTO;
use App\Ecommerce\Shipping\Services\ShippingManager;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;
use App\Settings\InventorySettings;
use Closure;

class ResolveShippingFeePipe
{
    protected $shippingManager;

    public function __construct(ShippingManager $shippingManager)
    {
        $this->shippingManager = $shippingManager;
    }

    public function handle(array $passable, Closure $next)
    {
        /** @var CheckoutRequestDTO $request */
        $request = $passable['request'];
        /** @var CheckoutResultDTO $result */
        $result = $passable['result'];

        if (!$request->shippingMethod || !$request->shippingAddress) {
            return $next($passable);
        }

        try {
            /** @var ShippingMethod $method */
            $method = ShippingMethod::query()->find($request->shippingMethod);

            if (!$method || !$method->is_enabled) {
                return $next($passable);
            }

            $driver = $this->shippingManager->driver($method->type);

            $totalPackages = 1;

            // 1. Split shipping logic: count participating inventories if enabled
            if (app(InventorySettings::class)->split_shipping_enabled) {
                $productIds = collect($request->items)->pluck('product_id')->toArray();
                $inventoriesCount = DB::table('shop_product_inventory_stocks')
                    ->whereIn('shop_product_id', $productIds)
                    ->where('stock_quantity', '>', 0)
                    ->distinct()
                    ->pluck('warehouse_id')
                    ->count();

                $totalPackages = max(1, $inventoriesCount);
            }

            $shippingCost = $driver->calculateFee(
                $result->subtotal,
                $request->shippingAddress->toArray(),
                array_merge($method->settings ?? [], ['currency' => $request->currency])
            );

            $finalShippingTotal = (int) ($shippingCost * $totalPackages);

            $result->shippingTotal = $finalShippingTotal;
            $result->total += $finalShippingTotal;
        } catch (\Exception $e) {
            // Log or handle error
        }

        return $next($passable);
    }
}
