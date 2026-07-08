<?php

namespace App\Ecommerce\Customer\Repositories;

use App\Ecommerce\Customer\Contracts\CustomerRepositoryInterface;
use App\Models\User;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentCustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    /**
     * EloquentCustomerRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * @inheritDoc
     */
    public function findByUserId(int|string $userId): ?User
    {
        return $this->model->find($userId);
    }
}
