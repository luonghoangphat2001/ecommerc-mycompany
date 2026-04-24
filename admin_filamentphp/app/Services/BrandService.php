<?php

namespace App\Services;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Services\BrandServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BrandService implements BrandServiceInterface
{
    protected $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function paginateBrands(int $perPage = 10, array $relations = []): LengthAwarePaginator
    {
        return $this->brandRepository->paginate($perPage, ['*'], $relations);
    }

    public function getAllBrands(array $relations = []): Collection
    {
        return $this->brandRepository->all(['*'], $relations);
    }

    public function getBrandById($id): ?Model
    {
        return $this->brandRepository->find($id);
    }

    public function createBrand(array $data): Model
    {
        return $this->brandRepository->create($data);
    }

    public function updateBrand($id, array $data): bool
    {
        return $this->brandRepository->update($id, $data);
    }

    public function deleteBrand($id): bool
    {
        return $this->brandRepository->delete($id);
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew(array $attributes): Model
    {
        return $this->brandRepository->firstOrNew($attributes);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail($id): Model
    {
        return $this->brandRepository->findOrFail($id);
    }

    /**
     * @inheritDoc
     */
    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->brandRepository->query();
    }
}
