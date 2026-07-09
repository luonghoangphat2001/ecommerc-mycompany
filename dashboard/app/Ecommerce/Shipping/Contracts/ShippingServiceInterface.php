<?php

namespace App\Ecommerce\Shipping\Contracts;

use Illuminate\Database\Eloquent\Builder;

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
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableMethods(?string $country = null, ?string $state = null, ?string $postcode = null, ?string $ward = null, int $subtotal = 0);

    /**
     * Get query for ShippingZone table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
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
