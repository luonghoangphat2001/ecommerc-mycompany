<?php

namespace App\Ecommerce\Product\Contracts;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Scope query to rooms.
     */
    public function scopeRooms(Builder $builder): Builder;

    /**
     * Scope query to tours.
     */
    public function scopeTours(Builder $builder): Builder;

    /**
     * Search products by name.
     */
    public function searchByName(string $term, ?string $type = null, int $limit = 50): Collection;

    /**
     * Get count of products with stock below security levels.
     */
    public function getLowStockCount(): int;

    /**
     * Find product by slug.
     */
    public function findBySlug(string $slug, array $relations = []): ?Product;

    public function findBySku(string $sku): ?Product;

    public function findForCheckout(int $productId, bool $visibleOnly = false): ?Product;

    public function findByIds(array $productIds, array $relations = []): Collection;

    public function findVariantForProduct(int $variantId, int $productId, bool $activeOnly = true): ?ProductVariant;

    public function findVariantById(int $variantId): ?ProductVariant;

    public function paginateFiltered(int $perPage = 15, array $columns = ['*'], array $relations = [], array $filters = []);
}
