<?php

namespace App\Services\Shipping;

use Illuminate\Support\Manager;
use App\Services\Shipping\Drivers\FlatRateDriver;
use InvalidArgumentException;

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
     * @return \App\Services\Shipping\Drivers\FreeShippingDriver
     */
    public function createFreeShippingDriver()
    {
        return new \App\Services\Shipping\Drivers\FreeShippingDriver();
    }

    /**
     * Create Ahamove driver.
     *
     * @return mixed
     */
    public function createAhamoveDriver()
    {
        // Placeholder for future implementation
        // return new AhamoveDriver();
        throw new InvalidArgumentException("Ahamove driver not yet implemented.");
    }
}
