<?php

namespace App\Ecommerce\Analytics\Contracts;

use App\Ecommerce\Core\Repositories\BaseRepository;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;

interface WebhookLogRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Delete logs older than a specific date.
     *
     * @param \Carbon\Carbon $date
     * @return int
     */
    public function deleteOlderThan(\Carbon\Carbon $date): int;
}
