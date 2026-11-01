<?php

namespace App\Ecommerce\Shipping\Services;

use App\Ecommerce\Shipping\Carriers\CarrierManager;
use App\Ecommerce\Shipping\Services\Drivers\CarrierDriver;
use App\Ecommerce\Shipping\Services\Drivers\FlatRateDriver;
use App\Ecommerce\Shipping\Services\Drivers\FreeShippingDriver;
use App\Ecommerce\Shipping\Services\Drivers\WeightBasedDriver;
use Illuminate\Support\Manager;

class ShippingManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('shop.shipping.default', 'flat_rate');
    }

    /**
     * Create Flat Rate driver.
     *
     * @return FlatRateDriver
     */
    public function createFlatRateDriver()
    {
        return new FlatRateDriver();
    }

    /**
     * Create Free Shipping driver.
     *
     * @return FreeShippingDriver
     */
    public function createFreeShippingDriver()
    {
        return new FreeShippingDriver();
    }

    /**
     * Create Weight Based driver.
     *
     * @return WeightBasedDriver
     */
    public function createWeightBasedDriver()
    {
        return new WeightBasedDriver();
    }

    /**
     * Create Local Pickup driver.
     *
     * @return FlatRateDriver
     */
    public function createLocalPickupDriver()
    {
        return new FlatRateDriver();
    }

    /**
     * Create Carrier driver.
     *
     * @return CarrierDriver
     */
    public function createCarrierDriver()
    {
        return new CarrierDriver($this->container->make(CarrierManager::class));
    }
}
