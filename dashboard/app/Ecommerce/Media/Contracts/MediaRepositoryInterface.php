<?php

namespace App\Ecommerce\Media\Contracts;

use App\Models\Media;

interface MediaRepositoryInterface
{
    /**
     * Find media by ID.
     */
    public function findById(int $id): ?Media;
}
