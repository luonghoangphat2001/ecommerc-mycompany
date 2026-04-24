<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PostRepositoryInterface;
use App\Models\Post;
use App\Repositories\Eloquent\BaseRepository;

class EloquentPostRepository extends BaseRepository implements PostRepositoryInterface
{
    /**
     * EloquentPostRepository constructor.
     *
     * @param Post $model
     */
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function getByCategorySlug(string $categorySlug, int $perPage = 10)
    {
        return $this->model->whereHas('category', function($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        })->paginate($perPage);
    }
}
