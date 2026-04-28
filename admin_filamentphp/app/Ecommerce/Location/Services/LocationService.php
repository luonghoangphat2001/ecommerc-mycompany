<?php

namespace App\Ecommerce\Location\Services;

use App\Ecommerce\Location\Contracts\CountryRepositoryInterface;
use App\Ecommerce\Location\Contracts\LocationServiceInterface;

class LocationService implements LocationServiceInterface
{
    protected $countryRepository;

    public function __construct(CountryRepositoryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * @inheritDoc
     */
    public function getCountryOptions(): array
    {
        return $this->countryRepository->getAllAsOptions();
    }

    /**
     * @inheritDoc
     */
    public function getCountryName(?string $code): ?string
    {
        return $this->countryRepository->findNameByCode($code);
    }
}
