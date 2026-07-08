<?php

namespace App\Ecommerce\User\Repositories;

use App\Ecommerce\User\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentUserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * EloquentUserRepository constructor.
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
}
