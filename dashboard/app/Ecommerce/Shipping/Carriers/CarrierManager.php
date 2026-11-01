<?php

namespace App\Ecommerce\Shipping\Carriers;

use App\Ecommerce\Shipping\Carriers\Contracts\CarrierAdapterInterface;
use App\Ecommerce\Shipping\Carriers\Adapters\GhnCarrierAdapter;
use App\Ecommerce\Shipping\Carriers\Adapters\GhtkCarrierAdapter;
use App\Ecommerce\Shipping\Carriers\Adapters\ViettelPostCarrierAdapter;
use App\Ecommerce\Shipping\Carriers\Adapters\VnPostCarrierAdapter;
use App\Ecommerce\Shipping\Carriers\Adapters\DhlCarrierAdapter;
use App\Ecommerce\Shipping\Carriers\Adapters\FedExCarrierAdapter;
use App\Ecommerce\Shipping\Carriers\Adapters\UpsCarrierAdapter;
use InvalidArgumentException;

class CarrierManager
{
    /**
     * @var array<string, CarrierAdapterInterface>
     */
    protected array $adapters = [];

    public function __construct()
    {
        $this->registerDefaultAdapters();
    }

    /**
     * Register standard supported adapters
     */
    protected function registerDefaultAdapters(): void
    {
        $this->registerAdapter(new GhnCarrierAdapter());
        $this->registerAdapter(new GhtkCarrierAdapter());
        $this->registerAdapter(new ViettelPostCarrierAdapter());
        $this->registerAdapter(new VnPostCarrierAdapter());
        $this->registerAdapter(new DhlCarrierAdapter());
        $this->registerAdapter(new FedExCarrierAdapter());
        $this->registerAdapter(new UpsCarrierAdapter());
    }

    /**
     * Register a carrier adapter
     */
    public function registerAdapter(CarrierAdapterInterface $adapter): void
    {
        $this->adapters[$adapter->getId()] = $adapter;
    }

    /**
     * Get a carrier adapter by ID
     */
    public function getAdapter(string $id): ?CarrierAdapterInterface
    {
        return $this->adapters[$id] ?? null;
    }

    /**
     * Get all registered carriers
     *
     * @return array<string, CarrierAdapterInterface>
     */
    public function getAllCarriers(): array
    {
        return $this->adapters;
    }

    /**
     * Get list of enabled carriers
     *
     * @return array<string, CarrierAdapterInterface>
     */
    public function getEnabledCarriers(): array
    {
        return array_filter($this->adapters, fn(CarrierAdapterInterface $carrier) => $carrier->isEnabled());
    }

    /**
     * Test connection for a carrier
     */
    public function testConnection(string $id): array
    {
        $adapter = $this->getAdapter($id);
        if (!$adapter) {
            throw new InvalidArgumentException("Carrier [{$id}] is not supported.");
        }

        return $adapter->testConnection();
    }

    /**
     * Save carrier configuration
     */
    public function saveCarrierConfig(string $id, array $data): void
    {
        $adapter = $this->getAdapter($id);
        if (!$adapter || !method_exists($adapter, 'saveConfig')) {
            throw new InvalidArgumentException("Carrier [{$id}] cannot be configured.");
        }

        $adapter->saveConfig($data);
    }

    /**
     * Get enabled carriers supporting a destination
     */
    public function getCarriersForDestination(string $country, ?string $state = null): array
    {
        return array_filter($this->getEnabledCarriers(), function (CarrierAdapterInterface $carrier) use ($country, $state) {
            return $carrier->supportsDestination($country, $state);
        });
    }
}
