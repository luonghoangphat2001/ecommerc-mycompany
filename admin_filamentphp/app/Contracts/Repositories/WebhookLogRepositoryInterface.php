<?php

namespace App\Contracts\Repositories;

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
