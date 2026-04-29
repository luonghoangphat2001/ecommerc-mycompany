<?php

namespace App\Ecommerce\Cart\Contracts;

interface CartServiceInterface
{
    /**
     * Sync and validate cart items.
     *
     * @param array $items
     * @return array
     */
    public function syncAndValidate(array $items): array;
}
