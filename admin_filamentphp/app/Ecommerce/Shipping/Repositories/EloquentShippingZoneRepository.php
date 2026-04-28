<?php

namespace App\Ecommerce\Shipping\Repositories;

use App\Ecommerce\Shipping\Contracts\ShippingZoneRepositoryInterface;
use App\Models\ShippingZone;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentShippingZoneRepository extends BaseRepository implements ShippingZoneRepositoryInterface
{
    /**
     * EloquentShippingZoneRepository constructor.
     *
     * @param ShippingZone $model
     */
    public function __construct(ShippingZone $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findMatchingZone(?string $country, ?string $state = null, ?string $postcode = null, ?string $ward = null): ?ShippingZone
    {
        if (!$country) {
            return null;
        }

        // Fetch all zones ordered by sort to perform matching in PHP
        // This handles complex JSON matching (nested arrays) more reliably across DB drivers
        $zones = $this->model->newQuery()->orderBy('sort')->get();

        foreach ($zones as $zone) {
            // Check standardize 'locations' JSON
            if (!empty($zone->locations) && is_array($zone->locations)) {
                foreach ($zone->locations as $location) {
                    if (($location['country'] ?? null) === $country) {
                        $provinces = $location['provinces'] ?? [];
                        if (empty($provinces) || (isset($state) && in_array($state, $provinces))) {
                            return $zone;
                        }
                    }
                }
            }
        }

        return null;
    }
}
