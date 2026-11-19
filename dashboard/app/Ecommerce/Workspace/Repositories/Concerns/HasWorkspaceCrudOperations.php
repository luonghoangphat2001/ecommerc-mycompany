<?php

namespace App\Ecommerce\Workspace\Repositories\Concerns;

trait HasWorkspaceCrudOperations
{
    protected function createWorkspaceModel(string $modelClass, array $data)
    {
        return $modelClass::query()->create($data);
    }

    protected function updateWorkspaceModel(string $modelClass, int | string $id, array $data)
    {
        $model = $modelClass::query()->findOrFail($id);
        $model->update($data);

        return $model;
    }

    protected function deleteWorkspaceModel(string $modelClass, int | string $id): bool
    {
        return (bool) $modelClass::query()->findOrFail($id)->delete();
    }
}
