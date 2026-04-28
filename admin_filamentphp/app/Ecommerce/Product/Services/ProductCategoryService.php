<?php

namespace App\Ecommerce\Product\Services;

use App\Ecommerce\Product\Contracts\ProductCategoryRepositoryInterface;
use App\Ecommerce\Product\Contracts\ProductCategoryServiceInterface;
use App\Models\ProductCategory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class ProductCategoryService implements ProductCategoryServiceInterface
{
    protected $categoryRepository;

    public function __construct(ProductCategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(array $relations = []): Collection
    {
        return $this->categoryRepository->all($relations);
    }

    public function createCategory(array $data)
    {
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(ProductCategory $category, array $data)
    {
        return $this->categoryRepository->update($category->id, $data);
    }

    public function deleteCategory(ProductCategory $category)
    {
        return $this->categoryRepository->delete($category->id);
    }

    public function getTreeSortedIds(): array
    {
        return $this->categoryRepository->getTreeSortedIds();
    }

    public function applyTreeSorting(Builder $query): Builder
    {
        return $this->categoryRepository->applyTreeSorting($query);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->categoryRepository->paginate($perPage, $columns, $relations);
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew(array $attributes): ProductCategory
    {
        return $this->categoryRepository->firstOrNew($attributes);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int|string $id): ProductCategory
    {
        return $this->categoryRepository->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function getTableQuery(): Builder
    {
        return $this->categoryRepository->query();
    }
}
