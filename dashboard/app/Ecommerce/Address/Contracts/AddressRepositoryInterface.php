<?php

namespace App\Ecommerce\Address\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use App\Models\Address;

interface AddressRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get addresses belonging to a user/customer.
     *
     * @param int $customerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCustomerId(int $customerId);

    /**
     * Associate address with customer via morph relation.
     *
     * @param int $addressId
     * @param int $customerId
     * @return void
     */
    public function associateWithCustomer(int $addressId, int $customerId);

    /**
     * Dissociate address from customer.
     *
     * @param int $addressId
     * @param int $customerId
     * @return void
     */
    public function dissociateFromCustomer(int $addressId, int $customerId);
}
