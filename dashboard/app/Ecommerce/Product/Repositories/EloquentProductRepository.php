<?php

namespace App\Ecommerce\Product\Repositories;

use App\Ecommerce\Core\Helpers\CatalogQueryFilters;
use App\Ecommerce\Core\Repositories\BaseRepository;
use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * EloquentProductRepository constructor.
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritDoc}
     */
    public function scopeRooms(Builder $builder): Builder
    {
        return $builder->where('type', 'room');
    }

    /**
     * {@inheritDoc}
     */
    public function scopeTours(Builder $builder): Builder
    {
        return $builder->where('type', 'tour');
    }

    /**
     * {@inheritDoc}
     */
    public function searchByName(string $term, ?string $type = null, int $limit = 50): Collection
    {
        $query = $this->model->newQuery()
            ->where('name', 'like', "%{$term}%")
            ->limit($limit);

        if ($type === 'room') {
            $this->scopeRooms($query);
        } elseif ($type === 'tour') {
            $this->scopeTours($query);
        }

        return $query->pluck('name', 'id');
    }

    /**
     * {@inheritDoc}
     */
    public function getLowStockCount(): int
    {
        return $this->model->whereColumn('qty', '<', 'security_stock')->count();
    }

    /**
     * Find product by slug.
     */
    public function findBySlug(string $slug, array $relations = []): ?Product
    {
        $query = $this->model->where('slug', $slug);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->newQuery()->where('sku', $sku)->first();
    }

    public function findForCheckout(int $productId, bool $visibleOnly = false): ?Product
    {
        $query = $this->model->newQuery()
            ->with(['comboComponents.product', 'comboComponents.variant', 'variants']);

        if ($visibleOnly) {
            $query->where('is_visible', true);
        }

        return $query->find($productId);
    }

    public function findByIds(array $productIds, array $relations = []): Collection
    {
        return $this->model->newQuery()
            ->when($relations !== [], fn (Builder $query) => $query->with($relations))
            ->whereIn('id', $productIds)
            ->get();
    }

    public function findVariantForProduct(int $variantId, int $productId, bool $activeOnly = true): ?ProductVariant
    {
        $query = ProductVariant::query()
            ->whereKey($variantId)
            ->where('product_id', $productId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->first();
    }

    public function findVariantById(int $variantId): ?ProductVariant
    {
        return ProductVariant::query()->find($variantId);
    }

    /**
     * Paginate products with storefront filters and sorting.
     */
    public function paginateFiltered(int $perPage = 15, array $columns = ['*'], array $relations = [], array $filters = [])
    {
        $query = $this->model->newQuery();

        if (! empty($relations)) {
            $query->with($relations);
        }

        CatalogQueryFilters::visibility($query, $filters);

        $search = CatalogQueryFilters::searchTerm($filters);
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categoryFilter = $filters['category_id'] ?? $filters['category'] ?? null;
        CatalogQueryFilters::relation($query, $categoryFilter, 'categories', 'shop_categories.id', 'shop_categories.slug');

        $brandFilter = $filters['brand_id'] ?? $filters['brand'] ?? null;
        if ($brandFilter !== null && $brandFilter !== '' && $brandFilter !== 'all') {
            if (is_numeric($brandFilter)) {
                $query->where('shop_brand_id', (int) $brandFilter);
            } else {
                $query->whereHas('brand', function (Builder $q) use ($brandFilter) {
                    $q->where('slug', (string) $brandFilter);
                });
            }
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $query->where('featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        $sort = strtolower((string) ($filters['sort'] ?? $filters['sort_by'] ?? 'newest'));
        switch ($sort) {
            case 'price_asc':
            case 'price-asc':
            case 'price_low':
                $query->orderBy('price', 'asc');

                break;
            case 'price_desc':
            case 'price-desc':
            case 'price_high':
                $query->orderBy('price', 'desc');

                break;
            case 'name_asc':
            case 'name-asc':
            case 'name':
                $query->orderBy('name', 'asc');

                break;
            case 'name_desc':
            case 'name-desc':
                $query->orderBy('name', 'desc');

                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');

                break;
            case 'newest':
            case 'latest':
            default:
                $query->orderByDesc('created_at')->orderByDesc('id');

                break;
        }

        return $query->paginate($perPage, $columns);
    }
}
