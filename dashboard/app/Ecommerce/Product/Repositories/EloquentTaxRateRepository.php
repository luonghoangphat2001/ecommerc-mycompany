<?php

namespace App\Ecommerce\Product\Repositories;

use App\Ecommerce\Product\Contracts\TaxRateRepositoryInterface;
use App\Models\TaxRate;
use App\Ecommerce\Core\Repositories\BaseRepository;

class EloquentTaxRateRepository extends BaseRepository implements TaxRateRepositoryInterface
{
    /**
     * EloquentTaxRateRepository constructor.
     *
     * @param TaxRate $model
     */
    public function __construct(TaxRate $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function findMatchingRate(int|string $taxClassId, ?string $country = null, ?string $state = null, ?string $city = null)
    {
        $query = $this->model->where('tax_class_id', $taxClassId)
            ->orderBy('priority', 'asc')
            ->orderBy('country', 'desc') // Country-specific first
            ->orderBy('state', 'desc')   // State-specific first
            ->orderBy('city', 'desc');   // City-specific first

        if ($country) {
            $query->where(function ($q) use ($country, $state, $city) {
                // Exact match or country-wide match or global match
                $q->where(function ($sub) use ($country, $state, $city) {
                    $sub->where('country', $country);
                    
                    if ($state) {
                        $sub->where(function ($s) use ($state, $city) {
                            $s->where('state', $state)
                              ->orWhereNull('state');
                        });
                    } else {
                        $sub->whereNull('state');
                    }

                    if ($city) {
                        $sub->where(function ($c) use ($city) {
                            $c->where('city', $city)
                              ->orWhereNull('city');
                        });
                    } else {
                        $sub->whereNull('city');
                    }
                })
                ->orWhereNull('country');
            });
        } else {
            $query->whereNull('country');
        }

        return $query->first();
    }
}
