<?php

namespace App\Ecommerce\Product\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BrandServiceInterface
{
    public function paginateBrands(int $perPage = 10, array $relations = []): LengthAwarePaginator;

    public function getAllBrands(array $relations = []): Collection;

    public function getBrandById($id): ?Model;

    public function createBrand(array $data): Model;

    public function updateBrand($id, array $data): bool;

    public function deleteBrand($id): bool;

    public function firstOrNew(array $attributes): Model;

    public function findOrFail($id): Model;

    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
