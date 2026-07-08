<?php

namespace App\Ecommerce\Combo\Repositories;

use App\Ecommerce\Combo\Contracts\ComboRepositoryInterface;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Models\ComboProduct;
use Illuminate\Support\Collection;

class EloquentComboRepository extends BaseRepository implements ComboRepositoryInterface
{
    /**
     * EloquentComboRepository constructor.
     *
     * @param mixed $model
     */
    public function __construct($model = null)
    {
        // Combo doesn't have a single model, so we skip parent constructor
    }

    /**
     * @inheritDoc
     */
    public function getActiveCombos(): Collection
    {
        return collect(ComboProduct::with('items')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('sort_order')
            ->get());
    }

    /**
     * @inheritDoc
     */
    public function getComboBySlug(string $slug)
    {
        return ComboProduct::with('items')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();
    }
}
