<?php

namespace App\Ecommerce\Location\Contracts;

interface CountryRepositoryInterface
{
    /**
     * Find country name by its 2-letter code.
     *
     * @param string|null $code
     * @return string|null
     */
    public function findNameByCode(?string $code): ?string;

    /**
     * Get all countries as an associative array [code => name].
     *
     * @return array
     */
    public function getAllAsOptions(): array;
}
