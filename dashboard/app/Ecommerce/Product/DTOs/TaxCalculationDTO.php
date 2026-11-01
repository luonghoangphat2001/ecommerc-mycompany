<?php

namespace App\Ecommerce\Product\DTOs;

use App\Enums\TaxRateStatus;
use Illuminate\Contracts\Support\Arrayable;

class TaxCalculationDTO implements Arrayable
{
    /**
     * @param int $amount
     * @param TaxRateStatus $status
     * @param string $rateName
     * @param float $ratePercent
     * @param int|null $taxRateId
     * @param array $breakdown
     */
    public function __construct(
        public int $amount,
        public TaxRateStatus $status,
        public string $rateName,
        public float $ratePercent,
        public ?int $taxRateId = null,
        public array $breakdown = []
    ) {}

    /**
     * Convert the DTO to an associative array for contract backward compatibility.
     *
     * @return array{amount: int, status: string, rate_name: string, rate_percent: float, tax_rate_id: ?int, breakdown: array}
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'status' => $this->status->value,
            'rate_name' => $this->rateName,
            'rate_percent' => $this->ratePercent,
            'tax_rate_id' => $this->taxRateId,
            'breakdown' => $this->breakdown,
        ];
    }
}
