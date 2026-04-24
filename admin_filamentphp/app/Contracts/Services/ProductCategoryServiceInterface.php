<?php

namespace App\Contracts\Services;

use App\Models\ProductCategory;

interface ProductCategoryServiceInterface
{
    public function getAllCategories(array $relations = []);

    public function createCategory(array $data);

    public function updateCategory(ProductCategory $category, array $data);

    public function deleteCategory(ProductCategory $category);

    public function getTreeSortedIds(): array;

    public function applyTreeSorting(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): \Illuminate\Pagination\LengthAwarePaginator;

    public function firstOrNew(array $attributes): ProductCategory;

    public function findOrFail(int|string $id): ProductCategory;

    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder;
}
