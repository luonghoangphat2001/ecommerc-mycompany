<?php

namespace App\Ecommerce\CrossSell\Services;

use App\Ecommerce\CrossSell\Contracts\CrossSellRepositoryInterface;
use App\Ecommerce\CrossSell\Contracts\CrossSellServiceInterface;
use App\Settings\MarketingSettings;
use Illuminate\Support\Collection;

class CrossSellService implements CrossSellServiceInterface
{
    protected CrossSellRepositoryInterface $crossSellRepository;

    public function __construct(
        CrossSellRepositoryInterface $crossSellRepository,
        protected MarketingSettings $marketingSettings
    ) {
        $this->crossSellRepository = $crossSellRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getCrossSellsForProduct(int $productId): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        return $this->crossSellRepository->getCrossSellsForProduct($productId);
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->marketingSettings->cross_sell_enabled ?? false);
    }
}
