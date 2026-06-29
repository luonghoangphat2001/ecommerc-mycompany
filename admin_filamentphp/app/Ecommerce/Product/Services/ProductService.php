<?php

namespace App\Ecommerce\Product\Services;

use App\Ecommerce\Product\Contracts\ProductRepositoryInterface;
use App\Ecommerce\Product\Contracts\ProductServiceInterface;
use App\Models\Product;
use App\Traits\HandleTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Settings\CheckoutSettings;

class ProductService implements ProductServiceInterface
{
    use HandleTransactions;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function createProduct(array $data, array $categories = []): Product
    {
        return $this->useTransaction(function () use ($data, $categories) {
            /** @var Product $product */
            $product = $this->productRepository->create($data);

            if (!empty($categories)) {
                $product->categories()->sync($categories);
            }

            return $product;
        });
    }

    /**
     * @inheritDoc
     */
    public function updateProduct(Product $product, array $data, array $categories = []): Product
    {
        return $this->useTransaction(function () use ($product, $data, $categories) {
            $this->productRepository->update($product->id, $data);

            if (isset($categories)) {
                $product->categories()->sync($categories);
            }

            return $product->fresh();
        });
    }

    /**
     * @inheritDoc
     */
    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product->id);
    }

    /**
     * @inheritDoc
     */
    public function calculateDisplayPrice(Product $product): int
    {
        $settings = app(CheckoutSettings::class);
        $price = $product->price;

        if ($settings->prices_include_tax) {
            $rate = $product->taxClass?->rates?->first()?->rate ?? 0;
            return (int) ($price * (1 + ($rate / 100)));
        }

        return $price;
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, $columns, $relations);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Product
    {
        /** @var Product */
        return $this->productRepository->findOrFail($id, $columns, $relations);
    }

    /**
     * Find product by slug.
     */
    public function findBySlug(string $slug, array $relations = []): ?Product
    {
        /** @var Product */
        return $this->productRepository->findBySlug($slug, $relations);
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew(array $attributes): Product
    {
        return $this->productRepository->firstOrNew($attributes);
    }

    /**
     * @inheritDoc
     */
    public function find(int|string $id): ?Product
    {
        return $this->productRepository->find($id);
    }

    /**
     * @inheritDoc
     */
    public function searchByName(string $search, ?string $type = null, int $limit = 50): array
    {
        return $this->productRepository->searchByName($search, $type, $limit)->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getLowStockCount(): int
    {
        return $this->productRepository->getLowStockCount();
    }

    /**
     * @inheritDoc
     */
    public function getTableQuery(): Builder
    {
        return $this->productRepository->query()
            ->with(['featuredImage', 'categories', 'brand']);
    }
}
