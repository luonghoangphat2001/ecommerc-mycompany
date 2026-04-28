<?php

namespace App\Ecommerce\Order\Repositories;

use App\Ecommerce\Order\Contracts\PaymentRepositoryInterface;
use App\Models\Payment;
use App\Ecommerce\Core\Repositories\BaseRepository;

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
