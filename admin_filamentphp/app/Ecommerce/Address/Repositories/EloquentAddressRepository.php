<?php

namespace App\Ecommerce\Address\Repositories;

use App\Ecommerce\Address\Contracts\AddressRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Models\Address;
use App\Models\Customer;

class EloquentAddressRepository extends BaseRepository implements AddressRepositoryInterface
{
    /**
     * EloquentAddressRepository constructor.
     *
     * @param Address $model
     */
    public function __construct(Address $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByCustomerId(int $customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return collect();
        }
        return $customer->addresses;
    }

    /**
     * @inheritDoc
     */
    public function associateWithCustomer(int $addressId, int $customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $customer->addresses()->syncWithoutDetaching([$addressId]);
        }
    }

    /**
     * @inheritDoc
     */
    public function dissociateFromCustomer(int $addressId, int $customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $customer->addresses()->detach($addressId);
        }
    }
}
