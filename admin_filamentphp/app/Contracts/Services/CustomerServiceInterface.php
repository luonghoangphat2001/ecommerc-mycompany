<?php

namespace App\Contracts\Services;

use App\Models\User;

interface CustomerServiceInterface
{
    /**
     * Create a new customer profile.
     *
     * @param array $data
     * @return User
     */
    public function createCustomer(array $data): User;

    /**
     * Update customer profile.
     *
     * @param User $customer
     * @param array $data
     * @return User
     */
    public function updateCustomer(User $customer, array $data): User;

    /**
     * Get customer or create if not exists by email.
     *
     * @param string $email
     * @param array $data
     * @return User
     */
    public function getOrCreateByEmail(string $email, array $data = []): User;

    /**
     * Get first or new customer.
     *
     * @param array $attributes
     * @return User
     */
    public function firstOrNew(array $attributes): User;

    /**
     * Find customer or fail.
     *
     * @param int|string $id
     * @return User
     */
    public function findOrFail(int|string $id): User;

    /**
     * Get query for table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
