<?php

namespace App\Ecommerce\Combo\Services;

use App\Ecommerce\Combo\Contracts\ComboServiceInterface;
use App\Ecommerce\Combo\Contracts\ComboRepositoryInterface;
use Illuminate\Support\Collection;

class ComboService implements ComboServiceInterface
{
    protected ComboRepositoryInterface $comboRepository;

    public function __construct(ComboRepositoryInterface $comboRepository)
    {
        $this->comboRepository = $comboRepository;
    }

    /**
     * @inheritDoc
     */
    public function getActiveCombos(): Collection
    {
        if (!$this->isEnabled()) {
            return collect();
        }

        return $this->comboRepository->getActiveCombos();
    }

    /**
     * @inheritDoc
     */
    public function getComboBySlug(string $slug)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        return $this->comboRepository->getComboBySlug($slug);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return app(\App\Settings\MarketingSettings::class)->combo_enabled ?? false;
    }
}
