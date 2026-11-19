<?php

namespace App\Ecommerce\Shipping\Contracts;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ShippingServiceInterface
{
    /**
     * Get available shipping methods for a location.
     */
    public function getAvailableMethods(
        ?string $country = null,
        ?string $state = null,
        ?string $postcode = null,
        ?string $ward = null,
        int $subtotal = 0,
        array $package = []
    ): Collection;

    /**
     * Calculate fee for a specific shipping method.
     */
    public function calculateMethodFee(
        ShippingMethod | int | string $method,
        array $destination,
        int $subtotal = 0,
        array $package = []
    ): ?int;

    public function resolveMethod(ShippingMethod | int | string | null $method): ?ShippingMethod;

    public function resolveCheckoutMethod(?ShippingZone $zone, int | string | null $method): ?ShippingMethod;

    /**
     * Resolve the shipping zone matching a destination.
     */
    public function resolveMatchingZone(
        ?string $country = null,
        ?string $state = null,
        ?string $postcode = null,
        ?string $ward = null
    ): ?ShippingZone;

    /**
     * Get query for ShippingZone table.
     */
    public function getShippingZoneTableQuery(): Builder;

    /**
     * Validate shipping address data.
     *
     * @return array ['is_valid' => bool, 'errors' => array]
     */
    public function validateAddress(array $addressData): array;

    /**
     * Get location info based on postcode/address.
     */
    public function getLocationInfo(string $postcode): ?array;
}
