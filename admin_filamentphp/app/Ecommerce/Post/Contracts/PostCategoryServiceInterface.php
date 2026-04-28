<?php

namespace App\Ecommerce\Post\Contracts;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;


interface PostCategoryServiceInterface
{
    public function getAllCategories(array $relations = []);

    public function createCategory(array $data);

    public function updateCategory(PostCategory $category, array $data);

    public function deleteCategory(PostCategory $category);

    public function getTreeSortedIds(): array;

    public function applyTreeSorting(Builder $query): Builder;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);

    public function getPaginatedPostsByCategory(PostCategory $category, int $perPage = 15);

    public function firstOrNew(array $attributes): PostCategory;

    public function findOrFail(int|string $id): PostCategory;

    public function getTableQuery(): Builder;
}
