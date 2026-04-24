<?php

namespace App\Contracts\Services;

use App\Models\Product;

interface ProductServiceInterface
{
    /**
     * Create a new product.
     *
     * @param array $data
     * @param array $categories
     * @return Product
     */
    public function createProduct(array $data, array $categories = []): Product;

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @param array $categories
     * @return Product
     */
    public function updateProduct(Product $product, array $data, array $categories = []): Product;

    /**
     * Delete a product.
     *
     * @param Product $product
     * @return bool
     */
    public function deleteProduct(Product $product): bool;

    /**
     * Calculate display price based on shop tax settings.
     *
     * @param Product $product
     * @return int
     */
    public function calculateDisplayPrice(Product $product): int;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);

    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Product;

    public function find(int|string $id): ?Product;

    public function firstOrNew(array $attributes): Product;

    public function searchByName(string $search, ?string $type = null, int $limit = 50): array;

    public function getLowStockCount(): int;

    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
