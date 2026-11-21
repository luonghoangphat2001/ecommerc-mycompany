<?php

namespace App\Ecommerce\Media\Repositories;

use App\Ecommerce\Media\Contracts\MediaRepositoryInterface;
use App\Models\Media;

class EloquentMediaRepository implements MediaRepositoryInterface
{
    /**
     * Find media by ID.
     */
    public function findById(int $id): ?Media
    {
        return Media::query()->find($id);
    }
}
