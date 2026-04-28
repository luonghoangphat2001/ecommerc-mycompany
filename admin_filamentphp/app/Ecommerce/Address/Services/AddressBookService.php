<?php

namespace App\Ecommerce\Address\Services;

use App\Ecommerce\Address\Contracts\AddressBookServiceInterface;
use App\Ecommerce\Address\Contracts\AddressRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AddressBookService implements AddressBookServiceInterface
{
    protected AddressRepositoryInterface $addressRepository;

    public function __construct(AddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * @inheritDoc
     */
    public function listAddresses(int $customerId)
    {
        return $this->addressRepository->getByCustomerId($customerId);
    }

    /**
     * @inheritDoc
     */
    public function addAddress(int $customerId, array $data)
    {
        return DB::transaction(function () use ($customerId, $data) {
            $address = $this->addressRepository->create($data);
            $this->addressRepository->associateWithCustomer($address->id, $customerId);
            return $address;
        });
    }

    /**
     * @inheritDoc
     */
    public function updateAddress(int $customerId, int $addressId, array $data)
    {
        // Verify relationship exists
        $addresses = $this->listAddresses($customerId);
        if (!$addresses->contains('id', $addressId)) {
            throw new \Exception(__('messages.address_not_found') ?? 'Address does not belong to customer');
        }

        return DB::transaction(function () use ($addressId, $data) {
            return $this->addressRepository->update($addressId, $data);
        });
    }

    /**
     * @inheritDoc
     */
    public function deleteAddress(int $customerId, int $addressId)
    {
        $addresses = $this->listAddresses($customerId);
        if (!$addresses->contains('id', $addressId)) {
            return false;
        }

        return DB::transaction(function () use ($customerId, $addressId) {
            $this->addressRepository->dissociateFromCustomer($addressId, $customerId);
            $this->addressRepository->delete($addressId);
            return true;
        });
    }
}
