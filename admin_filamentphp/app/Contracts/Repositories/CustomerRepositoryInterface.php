<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Models\User;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find customer by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get customer by user ID.
     *
     * @param int|string $userId
     * @return User|null
     */
    public function findByUserId(int|string $userId): ?User;
}
