<?php

namespace App\Ecommerce\Shipping\Carriers\Contracts;

use App\Models\Order;

interface CarrierAdapterInterface
{
    /**
     * Unique identifier (e.g. 'ghn', 'ghtk', 'dhl').
     */
    public function getId(): string;

    /**
     * Display name (e.g. 'Giao Hàng Nhanh (GHN)').
     */
    public function getName(): string;

    /**
     * Whether carrier supports domestic delivery (Vietnam).
     */
    public function isDomestic(): bool;

    /**
     * Whether carrier supports international shipping.
     */
    public function isInternational(): bool;

    /**
     * Get stored configuration for this carrier.
     */
    public function getConfig(): array;

    /**
     * Check if minimum required API credentials are configured.
     */
    public function isConfigured(): bool;

    /**
     * Check if carrier is enabled by Admin.
     */
    public function isEnabled(): bool;

    /**
     * Check if carrier is running in sandbox mode.
     */
    public function isSandbox(): bool;

    /**
     * Test live API connection with currently configured credentials.
     *
     * @return array ['success' => bool, 'message' => string, 'details' => array]
     */
    public function testConnection(): array;

    /**
     * Check if carrier can deliver to the given destination.
     */
    public function supportsDestination(string $country, ?string $state = null): bool;

    /**
     * Retrieve available shipping services for destination & package.
     *
     * @param array $destination ['country', 'state', 'city', 'ward', 'postal_code', 'address']
     * @param array $package ['weight' => int (grams), 'length' => int, 'width' => int, 'height' => int]
     * @return array List of available services
     */
    public function getAvailableServices(array $destination, array $package = []): array;

    /**
     * Calculate live shipping rate for a service.
     *
     * @param string $serviceCode
     * @param array $destination
     * @param array $package
     * @return array|null ['amount' => int, 'currency' => string, 'service_name' => string, 'estimated_days' => ?int]
     */
    public function calculateRate(string $serviceCode, array $destination, array $package = []): ?array;

    /**
     * Create shipment on carrier platform.
     *
     * @param Order $order
     * @param array $options
     * @return array ['success' => bool, 'tracking_number' => ?string, 'reference' => ?string, 'raw' => array]
     */
    public function createShipment(Order $order, array $options = []): array;

    /**
     * Cancel shipment.
     */
    public function cancelShipment(string $trackingNumber): bool;

    /**
     * Retrieve tracking info for a tracking number.
     *
     * @return array ['tracking_number' => string, 'status' => string, 'status_text' => string, 'history' => array]
     */
    public function getTracking(string $trackingNumber): array;
}
