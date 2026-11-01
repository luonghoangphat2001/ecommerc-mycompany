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
     *
     * @param string|null $country
     * @param string|null $state
     * @param string|null $postcode
     * @param string|null $ward
     * @param int $subtotal
     * @param array $package
     * @return Collection
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
        ShippingMethod|int|string $method,
        array $destination,
        int $subtotal = 0,
        array $package = []
    ): ?int;

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
     *
     * @return Builder
     */
    public function getShippingZoneTableQuery(): Builder;

    /**
     * Validate shipping address data.
     *
     * @param array $addressData
     * @return array ['is_valid' => bool, 'errors' => array]
     */
    public function validateAddress(array $addressData): array;

    /**
     * Get location info based on postcode/address.
     *
     * @param string $postcode
     * @return array|null
     */
    public function getLocationInfo(string $postcode): ?array;
}
