<?php

namespace App\Ecommerce\Combo\Services;

use App\Ecommerce\Combo\Contracts\ComboRepositoryInterface;
use App\Ecommerce\Combo\Contracts\ComboServiceInterface;
use App\Ecommerce\Settings\Contracts\FeaturePolicyServiceInterface;
use Illuminate\Support\Collection;

class ComboService implements ComboServiceInterface
{
    protected ComboRepositoryInterface $comboRepository;

    public function __construct(
        ComboRepositoryInterface $comboRepository,
        protected FeaturePolicyServiceInterface $featurePolicy
    ) {
        $this->comboRepository = $comboRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveCombos(): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        return $this->comboRepository->getActiveCombos();
    }

    /**
     * {@inheritDoc}
     */
    public function getComboBySlug(string $slug)
    {
        if (! $this->isEnabled()) {
            return null;
        }

        return $this->comboRepository->getComboBySlug($slug);
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled(): bool
    {
        return $this->featurePolicy->isComboEnabled();
    }
}
