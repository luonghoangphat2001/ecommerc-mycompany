<?php

namespace App\Observers;

use App\Ecommerce\Audit\Contracts\FinancialAuditServiceInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class PriceChangeAuditObserver
{
    public function __construct(
        private readonly FinancialAuditServiceInterface $auditService,
    ) {}

    public function updating(Model $model): void
    {
        if (! $model instanceof Product && ! $model instanceof ProductVariant) {
            return;
        }

        if (! $model->isDirty('price') && ! $model->isDirty('sale_price')) {
            return;
        }

        try {
            $oldSalePrice = $model->getOriginal('sale_price');
            $oldPrice = (float) ($model->getOriginal('price') ?? 0);
            $newPrice = (float) ($model->price ?? 0);
            $newSalePrice = $model->sale_price !== null ? (float) $model->sale_price : null;
            $oldSalePrice = $oldSalePrice !== null ? (float) $oldSalePrice : null;

            if ($model instanceof Product) {
                $this->auditService->logProductPriceChange(
                    $model,
                    $oldPrice,
                    $newPrice,
                    $oldSalePrice,
                    $newSalePrice,
                );

                return;
            }

            $this->auditService->logVariantPriceChange(
                $model,
                $oldPrice,
                $newPrice,
                $oldSalePrice,
                $newSalePrice,
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
