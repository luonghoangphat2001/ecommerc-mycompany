<?php

namespace App\Ecommerce\Shipping\Services;

use Illuminate\Support\Manager;
use App\Ecommerce\Shipping\Services\Drivers\FlatRateDriver;
use App\Ecommerce\Shipping\Services\Drivers\FreeShippingDriver;
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
     * @return FreeShippingDriver
     */
    public function createFreeShippingDriver()
    {
        return new FreeShippingDriver();
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
