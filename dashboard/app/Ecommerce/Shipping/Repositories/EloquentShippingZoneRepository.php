<?php

namespace App\Ecommerce\Shipping\Repositories;

use App\Ecommerce\Shipping\Contracts\ShippingZoneRepositoryInterface;
use App\Ecommerce\Shipping\Support\VietnamLocations;
use App\Models\ShippingZone;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentShippingZoneRepository extends BaseRepository implements ShippingZoneRepositoryInterface
{
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

        $countryUpper = strtoupper(trim($country));
        $stateTrimmed = $state ? trim($state) : null;

        // Fetch active zones ordered by sort priority
        $zones = $this->model->newQuery()
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn('shop_shipping_zones', 'is_active'),
                fn($q) => $q->where(fn($sub) => $sub->where('is_active', true)->orWhereNull('is_active'))
            )
            ->orderBy('sort')
            ->get();

        $specificProvinceMatch = null;
        $countryLevelMatch = null;
        $continentalMatch = null;
        $globalFallbackMatch = null;

        foreach ($zones as $zone) {
            if (empty($zone->locations) || !is_array($zone->locations)) {
                continue;
            }

            foreach ($zone->locations as $location) {
                $locCountry = strtoupper(trim($location['country'] ?? ''));

                // Global fallback ("*" or empty)
                if ($locCountry === '*' || $locCountry === 'ALL') {
                    if (!$globalFallbackMatch) {
                        $globalFallbackMatch = $zone;
                    }
                    continue;
                }

                // Direct country match
                if ($locCountry === $countryUpper) {
                    $provinces = $location['provinces'] ?? [];

                    // Specific province match
                    if ($stateTrimmed && !empty($provinces)) {
                        $isMatched = false;
                        if ($countryUpper === 'VN') {
                            $isMatched = VietnamLocations::matches($stateTrimmed, $provinces);
                        } else {
                            $stateNormalized = strtolower($stateTrimmed);
                            foreach ($provinces as $p) {
                                if (strcasecmp($p, $stateTrimmed) === 0 || strtolower(trim($p)) === $stateNormalized) {
                                    $isMatched = true;
                                    break;
                                }
                            }
                        }

                        if ($isMatched && !$specificProvinceMatch) {
                            $specificProvinceMatch = $zone;
                            break; // matched best zone for this level
                        }
                    } elseif (empty($provinces) || in_array('*', $provinces, true)) {
                        // Country-level match without province restrictions
                        if (!$countryLevelMatch) {
                            $countryLevelMatch = $zone;
                        }
                    }
                }

                // Check multiple countries in single location entry (e.g. continental list)
                $countriesList = $location['countries'] ?? [];
                if (!empty($countriesList) && is_array($countriesList)) {
                    $upperCountries = array_map('strtoupper', array_map('trim', $countriesList));
                    if (in_array($countryUpper, $upperCountries, true) && !$continentalMatch) {
                        $continentalMatch = $zone;
                    }
                }
            }

            // If we found a specific province match, return immediately since zones are sorted by priority
            if ($specificProvinceMatch) {
                return $specificProvinceMatch;
            }
        }

        return $specificProvinceMatch
            ?? $countryLevelMatch
            ?? $continentalMatch
            ?? $globalFallbackMatch;
    }
}
