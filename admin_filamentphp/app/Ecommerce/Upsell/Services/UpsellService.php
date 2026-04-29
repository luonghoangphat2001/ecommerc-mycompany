<?php

namespace App\Ecommerce\Upsell\Services;

use App\Ecommerce\Upsell\Contracts\UpsellServiceInterface;
use App\Ecommerce\Upsell\Contracts\UpsellRepositoryInterface;
use Illuminate\Support\Collection;

class UpsellService implements UpsellServiceInterface
{
    protected UpsellRepositoryInterface $upsellRepository;

    public function __construct(UpsellRepositoryInterface $upsellRepository)
    {
        $this->upsellRepository = $upsellRepository;
    }

    /**
     * @inheritDoc
     */
    public function getUpsellsForProduct(int $productId): Collection
    {
        if (!$this->isEnabled()) {
            return collect();
        }

        return $this->upsellRepository->getUpsellsForProduct($productId);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return app(\App\Settings\MarketingSettings::class)->upsell_enabled ?? false;
    }
}
