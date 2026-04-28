<?php

namespace App\Ecommerce\Location\Contracts;

interface LocationServiceInterface
{
    /**
     * Get all countries as an associative array [code => name].
     *
     * @return array
     */
    public function getCountryOptions(): array;

    /**
     * Find country name by its 2-letter code.
     *
     * @param string|null $code
     * @return string|null
     */
    public function getCountryName(?string $code): ?string;
}
