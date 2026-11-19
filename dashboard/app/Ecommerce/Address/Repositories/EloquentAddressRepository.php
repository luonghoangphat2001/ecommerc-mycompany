<?php

namespace App\Ecommerce\Address\Repositories;

use App\Ecommerce\Address\Contracts\AddressRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Models\Address;
use App\Models\User;

class EloquentAddressRepository extends BaseRepository implements AddressRepositoryInterface
{
    /**
     * EloquentAddressRepository constructor.
     */
    public function __construct(Address $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getByCustomerId(int $customerId)
    {
        $user = $this->findCustomer($customerId);
        if (! $user) {
            return collect();
        }

        return $user->addresses;
    }

    /**
     * {@inheritDoc}
     */
    public function associateWithCustomer(int $addressId, int $customerId)
    {
        $user = $this->findCustomer($customerId);
        if ($user) {
            $user->addresses()->syncWithoutDetaching([$addressId]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dissociateFromCustomer(int $addressId, int $customerId)
    {
        $user = $this->findCustomer($customerId);
        if ($user) {
            $user->addresses()->detach($addressId);
        }
    }

    private function findCustomer(int $customerId): ?User
    {
        return User::query()->find($customerId);
    }
}
