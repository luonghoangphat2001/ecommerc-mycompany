<?php

namespace App\Ecommerce\Product\Services;

use App\Models\TaxClass;
use App\Models\TaxRate;

use App\Ecommerce\Product\Contracts\TaxServiceInterface;
use App\Ecommerce\Product\Contracts\TaxRateRepositoryInterface;
use App\Ecommerce\Product\Contracts\TaxClassRepositoryInterface;
use App\Ecommerce\Settings\Contracts\SettingServiceInterface;

class TaxService implements TaxServiceInterface
{
    protected $taxRateRepository;
    protected $taxClassRepository;
    protected $settingService;

    public function __construct(
        TaxRateRepositoryInterface $taxRateRepository,
        TaxClassRepositoryInterface $taxClassRepository,
        SettingServiceInterface $settingService
    ) {
        $this->taxRateRepository = $taxRateRepository;
        $this->taxClassRepository = $taxClassRepository;
        $this->settingService = $settingService;
    }

    /**
     * Calculate tax for a given amount and tax class.
     * 
     * @param TaxClass $taxClass
     * @param int $amount (in cents/lowest denominator)
     * @param string|null $country (ISO 3166-1 alpha-2)
     * @return array Array containing 'amount' and 'rate_name'
     */
    public function calculate($taxClass, int $amount, ?string $country = null): array
    {
        // Get settings from SettingService
        $settings = $this->settingService->getSetting('tax', [
            'enabled' => true,
            'prices_include_tax' => false,
        ]);

        if (!($settings['enabled'] ?? true)) {
            return ['amount' => 0, 'rate_name' => 'Tax Disabled', 'rate_percent' => 0];
        }

        $includeTax = $settings['prices_include_tax'] ?? false;

        // Query best matching tax rate using repository
        $rate = $this->taxRateRepository->findMatchingRate($taxClass->id, $country);

        if (!$rate) {
            return [
                'amount' => 0,
                'rate_name' => 'No tax',
                'rate_percent' => 0
            ];
        }

        // Calculate amount
        $ratePercent = (float) $rate->rate;
        $taxAmount = 0;

        if ($includeTax) {
            // Amount is gross. Tax = Amount - (Amount / (1 + Rate))
            $taxAmount = (int) round($amount - ($amount / (1 + ($ratePercent / 100))));
        } else {
            // Amount is net. Tax = Amount * (Rate / 100)
            $taxAmount = (int) round($amount * ($ratePercent / 100));
        }

        return [
            'amount' => $taxAmount,
            'rate_name' => $rate->name,
            'rate_percent' => $ratePercent,
            'tax_rate_id' => $rate->id,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTaxClassTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->taxClassRepository->query();
    }
}
