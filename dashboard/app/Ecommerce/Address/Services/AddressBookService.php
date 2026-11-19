<?php

namespace App\Ecommerce\Address\Services;

use App\Ecommerce\Address\Contracts\AddressBookServiceInterface;
use App\Ecommerce\Address\Contracts\AddressRepositoryInterface;
use App\Traits\HandleTransactions;

class AddressBookService implements AddressBookServiceInterface
{
    use HandleTransactions;

    protected AddressRepositoryInterface $addressRepository;

    public function __construct(AddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function listAddresses(int $customerId)
    {
        return $this->addressRepository->getByCustomerId($customerId);
    }

    /**
     * {@inheritDoc}
     */
    public function addAddress(int $customerId, array $data)
    {
        return $this->useTransaction(function () use ($customerId, $data) {
            $address = $this->addressRepository->create($data);
            $this->addressRepository->associateWithCustomer($address->id, $customerId);

            return $address;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function updateAddress(int $customerId, int $addressId, array $data)
    {
        if (! $this->addressBelongsToCustomer($customerId, $addressId)) {
            throw new \Exception(__('messages.address_not_found') ?? 'Address does not belong to customer');
        }

        return $this->useTransaction(function () use ($addressId, $data) {
            return $this->addressRepository->update($addressId, $data);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAddress(int $customerId, int $addressId)
    {
        if (! $this->addressBelongsToCustomer($customerId, $addressId)) {
            return false;
        }

        return $this->useTransaction(function () use ($customerId, $addressId) {
            $this->addressRepository->dissociateFromCustomer($addressId, $customerId);
            $this->addressRepository->delete($addressId);

            return true;
        });
    }

    private function addressBelongsToCustomer(int $customerId, int $addressId): bool
    {
        return $this->listAddresses($customerId)->contains('id', $addressId);
    }
}
