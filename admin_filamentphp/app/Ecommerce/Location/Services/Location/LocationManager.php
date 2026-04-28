<?php

namespace App\Ecommerce\Location\Services\Location;

use App\Ecommerce\Location\Contracts\LocationProviderInterface;
use App\Ecommerce\Location\Services\Location\Providers\VietnamLocationProvider;
use App\Ecommerce\Location\Services\Location\Providers\GlobalLocationProvider;
use Illuminate\Support\Manager;

class LocationManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->config->get('shop.location.default', 'global');
    }

    public function createVietnamDriver(): LocationProviderInterface
    {
        return new VietnamLocationProvider();
    }

    public function createGlobalDriver(): LocationProviderInterface
    {
        return new GlobalLocationProvider();
    }
}
