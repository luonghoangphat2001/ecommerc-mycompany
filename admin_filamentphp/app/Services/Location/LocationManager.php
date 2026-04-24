<?php

namespace App\Services\Location;

use App\Contracts\Location\LocationProviderInterface;
use App\Services\Location\Providers\VietnamLocationProvider;
use App\Services\Location\Providers\GlobalLocationProvider;
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
