<?php

namespace App\Ecommerce\Analytics\Repositories;

use App\Ecommerce\Core\Repositories\BaseRepository;

use App\Ecommerce\Analytics\Contracts\WebhookLogRepositoryInterface;
use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Builder;

class EloquentWebhookLogRepository extends BaseRepository implements WebhookLogRepositoryInterface
{
    public function __construct(WebhookLog $model)
    {
        parent::__construct($model);
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * @inheritDoc
     */
    public function deleteOlderThan(\Carbon\Carbon $date): int
    {
        return $this->model->where('created_at', '<', $date)->delete();
    }
}
