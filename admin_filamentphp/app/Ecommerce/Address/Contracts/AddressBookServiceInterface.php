<?php

namespace App\Ecommerce\Address\Contracts;

interface AddressBookServiceInterface
{
    /**
     * List saved addresses for customer.
     *
     * @param int $customerId
     * @return \Illuminate\Support\Collection
     */
    public function listAddresses(int $customerId);

    /**
     * Add new address to customer's book.
     *
     * @param int $customerId
     * @param array $data
     * @return \App\Models\Address
     */
    public function addAddress(int $customerId, array $data);

    /**
     * Update existing address in book.
     *
     * @param int $customerId
     * @param int $addressId
     * @param array $data
     * @return \App\Models\Address
     */
    public function updateAddress(int $customerId, int $addressId, array $data);

    /**
     * Delete address from customer's book.
     *
     * @param int $customerId
     * @param int $addressId
     * @return bool
     */
    public function deleteAddress(int $customerId, int $addressId);
}
