<?php

namespace App\Ecommerce\Customer\Services;

use App\Ecommerce\Customer\Contracts\CustomerRepositoryInterface;
use App\Ecommerce\Customer\Contracts\CustomerServiceInterface;
use App\Models\User;
use App\Traits\HandleTransactions;

class CustomerService implements CustomerServiceInterface
{
    use HandleTransactions;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * CustomerService constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function createCustomer(array $data): User
    {
        return $this->useTransaction(function () use ($data) {
            /** @var User $user */
            $user = $this->customerRepository->create($data);
            return $user;
        });
    }

    /**
     * @inheritDoc
     */
    public function updateCustomer(User $customer, array $data): User
    {
        return $this->useTransaction(function () use ($customer, $data) {
            $this->customerRepository->update($customer->id, $data);
            return $customer->fresh();
        });
    }

    /**
     * @inheritDoc
     */
    public function getOrCreateByEmail(string $email, array $data = []): User
    {
        return $this->useTransaction(function () use ($email, $data) {
            /** @var User|null $customer */
            $customer = $this->customerRepository->findByEmail($email);

            if ($customer) {
                return $customer;
            }

            /** @var User $newCustomer */
            $newCustomer = $this->customerRepository->create(array_merge($data, [
                'email' => $email,
            ]));
            
            return $newCustomer;
        });
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew(array $attributes): User
    {
        /** @var User $user */
        $user = $this->customerRepository->firstOrNew($attributes);
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int|string $id): User
    {
        /** @var User $user */
        $user = $this->customerRepository->findOrFail($id);
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->customerRepository->query();
    }
}
