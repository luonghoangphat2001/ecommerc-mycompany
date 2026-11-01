<?php

namespace App\Ecommerce\Shipping\Services\Drivers;

use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Ecommerce\Shipping\Contracts\ShippingProviderInterface;
use App\Models\Order;

class CarrierDriver implements ShippingProviderInterface
{
    public function __construct(protected CarrierManager $carrierManager)
    {
    }

    /**
     * @inheritDoc
     */
    public function calculateFee(int $subtotal, array $destination, array $options = []): int
    {
        $carrierId = $options['carrier'] ?? $options['carrier_id'] ?? null;
        if (!$carrierId) {
            return (int) ($options['fallback_rate'] ?? $options['cost'] ?? 35000);
        }

        $carrier = $this->carrierManager->getAdapter($carrierId);
        if (!$carrier || !$carrier->isEnabled() || !$carrier->isConfigured()) {
            // Use explicit manual fallback rate if configured
            $fallback = $options['fallback_rate'] ?? $options['cost'] ?? null;
            return $fallback !== null ? (int) $fallback : -1;
        }

        $serviceCode = (string) ($options['service_code'] ?? 'standard');
        $package = [
            'weight' => (int) ($options['weight'] ?? 500),
            'length' => (int) ($options['length'] ?? 20),
            'width' => (int) ($options['width'] ?? 15),
            'height' => (int) ($options['height'] ?? 10),
        ];

        $quote = $carrier->calculateRate($serviceCode, array_merge($destination, ['subtotal' => $subtotal]), $package);
        if ($quote && isset($quote['amount'])) {
            return (int) $quote['amount'];
        }

        // When carrier API is unavailable, return configured fallback or -1
        $fallback = $options['fallback_rate'] ?? $options['cost'] ?? null;
        return $fallback !== null ? (int) $fallback : -1;
    }

    /**
     * @inheritDoc
     */
    public function createOrder(Order $order): string
    {
        $carrierId = $order->shipping?->metadata['carrier'] ?? 'ghn';
        $carrier = $this->carrierManager->getAdapter($carrierId);

        if ($carrier) {
            $result = $carrier->createShipment($order);
            if (!empty($result['tracking_number'])) {
                return (string) $result['tracking_number'];
            }
        }

        return 'CARRIER-' . $order->number . '-' . uniqid();
    }

    /**
     * @inheritDoc
     */
    public function cancelOrder(string $trackingNumber): bool
    {
        return true;
    }
}
