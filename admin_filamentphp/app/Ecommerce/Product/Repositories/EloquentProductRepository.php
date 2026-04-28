<?php

namespace App\Ecommerce\Product\Repositories;

use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Ecommerce\Core\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * EloquentProductRepository constructor.
     *
     * @param Product $model
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function scopeRooms(Builder $builder): Builder
    {
        return $builder->where('type', 'room');
    }

    /**
     * @inheritDoc
     */
    public function scopeTours(Builder $builder): Builder
    {
        return $builder->where('type', 'tour');
    }

    /**
     * @inheritDoc
     */
    public function searchByName(string $term, ?string $type = null, int $limit = 50): Collection
    {
        $query = $this->model->newQuery()
            ->where('name', 'like', "%{$term}%")
            ->limit($limit);

        if ($type === 'room') {
            $this->scopeRooms($query);
        } elseif ($type === 'tour') {
            $this->scopeTours($query);
        }

        return $query->pluck('name', 'id');
    }

    /**
     * @inheritDoc
     */
    public function getLowStockCount(): int
    {
        return $this->model->whereColumn('qty', '<', 'security_stock')->count();
    }
}
