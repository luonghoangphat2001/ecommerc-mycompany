<?php

namespace App\Ecommerce\Post\Services;

use App\Ecommerce\Post\Contracts\PostCategoryRepositoryInterface;
use App\Ecommerce\Post\Contracts\PostCategoryServiceInterface;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;

class PostCategoryService implements PostCategoryServiceInterface
{
    protected $categoryRepository;

    public function __construct(PostCategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(array $relations = [])
    {
        return $this->categoryRepository->all($relations);
    }

    public function createCategory(array $data)
    {
        return $this->categoryRepository->create($data);
    }

    public function updateCategory(PostCategory $category, array $data)
    {
        return $this->categoryRepository->update($category->id, $data);
    }

    public function deleteCategory(PostCategory $category)
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

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = [])
    {
        return $this->categoryRepository->paginate($perPage, $columns, $relations);
    }

    public function getPaginatedPostsByCategory(PostCategory $category, int $perPage = 15)
    {
        return $category->posts()->with(['author', 'categories'])->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew(array $attributes): PostCategory
    {
        return $this->categoryRepository->firstOrNew($attributes);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int|string $id): PostCategory
    {
        return $this->categoryRepository->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function getTableQuery(): Builder
    {
        return $this->categoryRepository->query()->where('type', 'post');
    }
}
