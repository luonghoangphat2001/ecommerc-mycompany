<?php

namespace App\Ecommerce\Location\Repositories;

use App\Ecommerce\Location\Contracts\CountryRepositoryInterface;
use Squire\Models\Country;

class EloquentCountryRepository implements CountryRepositoryInterface
{
    /**
     * Local static cache to prevent N+1 queries in tables.
     *
     * @var array|null
     */
    protected static ?array $cache = null;

    /**
     * @inheritDoc
     */
    public function findNameByCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        $countries = $this->getCachedCountries();

        return $countries[$code] ?? $code;
    }

    /**
     * @inheritDoc
     */
    public function getAllAsOptions(): array
    {
        return $this->getCachedCountries();
    }

    /**
     * Load and cache countries from Squire.
     *
     * @return array
     */
    protected function getCachedCountries(): array
    {
        if (static::$cache === null) {
            // Squire models use a static array internally, but we cache the pluck result for speed.
            static::$cache = Country::all()->pluck('name', 'id')->toArray();
        }

        return static::$cache;
    }
}
