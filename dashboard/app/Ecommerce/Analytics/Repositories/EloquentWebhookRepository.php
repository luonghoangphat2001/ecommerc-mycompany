<?php

namespace App\Ecommerce\Analytics\Repositories;

use App\Ecommerce\Core\Repositories\BaseRepository;

use App\Ecommerce\Analytics\Contracts\WebhookRepositoryInterface;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Builder;

class EloquentWebhookRepository extends BaseRepository implements WebhookRepositoryInterface
{
    public function __construct(Webhook $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }
}
