<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Models\Payment;
use App\Repositories\Eloquent\BaseRepository;

class EloquentPaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    /**
     * EloquentPaymentRepository constructor.
     *
     * @param Payment $model
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }
}
