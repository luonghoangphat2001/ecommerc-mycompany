<?php

namespace App\Ecommerce\Core\Repositories;

use App\Ecommerce\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * @inheritDoc
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(int|string $id, array $data): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->update($data);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(int|string $id): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    /**
     * @inheritDoc
     */
    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    /**
     * @inheritDoc
     */
    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }

    /**
     * @inheritDoc
     */
    public function firstOrNew(array $attributes, array $values = []): Model
    {
        return $this->model->firstOrNew($attributes, $values);
    }

    /**
     * @inheritDoc
     */
    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Tự động áp dụng filter, search, và sort từ query parameters.
     * 
     * @param array $filters (e.g. ['status' => 'active'])
     * @param string|null $search (e.g. 'keyword')
     * @param array $searchFields (e.g. ['name', 'description'])
     * @param string|null $sortBy (e.g. 'created_at')
     * @param string $sortDirection (e.g. 'desc')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filterAndSort(array $filters = [], ?string $search = null, array $searchFields = [], ?string $sortBy = null, string $sortDirection = 'desc'): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->query();

        // Apply filters
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        // Apply search
        if (!empty($search) && !empty($searchFields)) {
            $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            });
        }

        // Apply sort
        if (!empty($sortBy)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy($this->model->getKeyName(), 'desc');
        }

        return $query;
    }
}
