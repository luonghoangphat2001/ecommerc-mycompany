<?php

namespace App\Http\Controllers\Admin\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HasPermissionedServiceCrudActions
{
    abstract protected function crudService(): object;

    abstract protected function createServiceMethod(): string;

    abstract protected function updateServiceMethod(): string;

    abstract protected function deleteServiceMethod(): string;

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeCrudAction('create');
        $this->crudService()->{$this->createServiceMethod()}($this->validateRequest($request));

        return $this->redirectWithSuccess('Tạo mới thành công.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeCrudAction('update');
        $this->crudService()->{$this->updateServiceMethod()}($id, $this->validateRequest($request));

        return $this->redirectWithSuccess('Cập nhật thành công.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeCrudAction('delete');
        $this->crudService()->{$this->deleteServiceMethod()}($id);

        return $this->redirectWithSuccess('Xóa thành công.');
    }

    protected function authorizeCrudAction(string $action): void
    {
        if (! $this->hasPermission($action)) {
            abort(403);
        }
    }
}
