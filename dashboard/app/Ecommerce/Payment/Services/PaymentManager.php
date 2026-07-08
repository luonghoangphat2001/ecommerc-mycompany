<?php

namespace App\Ecommerce\Payment\Services;

use Illuminate\Support\Manager;
use App\Ecommerce\Payment\Services\Drivers\CODDriver;
use InvalidArgumentException;

class PaymentManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return config('shop.payment.default', 'cod');
    }

    /**
     * Create COD driver.
     *
     * @return CODDriver
     */
    public function createCodDriver()
    {
        return new CODDriver();
    }

    /**
     * Create Stripe driver.
     *
     * @return mixed
     */
    public function createStripeDriver()
    {
        // Placeholder for future implementation
        throw new InvalidArgumentException("Stripe driver not yet implemented.");
    }

    /**
     * Create VNPay driver.
     *
     * @return mixed
     */
    public function createVnpayDriver()
    {
        // Placeholder for future implementation
        throw new InvalidArgumentException("VNPay driver not yet implemented.");
    }
}
