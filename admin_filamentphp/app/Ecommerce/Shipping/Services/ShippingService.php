<?php

namespace App\Ecommerce\Shipping\Services;

use App\Models\ShippingZone;
use App\Models\ShippingMethod;

use App\Ecommerce\Shipping\Contracts\ShippingServiceInterface;
use App\Ecommerce\Shipping\Contracts\ShippingZoneRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class ShippingService implements ShippingServiceInterface
{
    protected $shippingZoneRepository;

    public function __construct(ShippingZoneRepositoryInterface $shippingZoneRepository)
    {
        $this->shippingZoneRepository = $shippingZoneRepository;
    }

    public function getAvailableMethods(?string $country = null, ?string $state = null, ?string $postcode = null, ?string $ward = null, int $subtotal = 0)
    {
        // Find zone that best matches the location using the repository
        $zone = $this->shippingZoneRepository->findMatchingZone($country, $state, $postcode, $ward);

        // Fallback to "Rest of World" zone if not found (getting the first one with no specific country restriction)
        if (!$zone) {
            $zone = $this->shippingZoneRepository->all()->first();
        }

        if (!$zone) {
            return collect();
        }

        $drivers = [
            'flat_rate' => \App\Ecommerce\Shipping\Services\Drivers\FlatRateDriver::class,
            'free_shipping' => \App\Ecommerce\Shipping\Services\Drivers\FreeShippingDriver::class,
        ];

        return $zone->methods()->where('is_enabled', true)->get()->map(function ($method) use ($subtotal, $country, $state, $postcode, $drivers) {
            $driverClass = $drivers[$method->type] ?? null;

            if (!$driverClass) {
                return [
                    'method_id' => $method->id,
                    'name' => $method->name,
                    'is_available' => false
                ];
            }

            $driver = app($driverClass);
            $cost = $driver->calculateFee($subtotal, [
                'country' => $country,
                'state' => $state,
                'postcode' => $postcode,
            ], ['cost' => $method->settings['cost'] ?? 0]);

            return [
                'method_id' => $method->id,
                'name' => $method->name,
                'type' => $method->type,
                'cost' => $cost,
                'is_available' => $cost !== null
            ];
        })->where('is_available', true);
    }

    /**
     * @inheritDoc
     */
    public function getShippingZoneTableQuery(): Builder
    {
        return $this->shippingZoneRepository->query();
    }
}
