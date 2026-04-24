<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\WebhookRepositoryInterface;
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
