<?php

namespace App\Http\Controllers\Admin;

use App\Ecommerce\User\Contracts\AdminUserServiceInterface;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends BaseCrudController
{
    public function __construct(private readonly AdminUserServiceInterface $adminUserService)
    {
        parent::__construct();
    }

    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'title' => __($this->title()),
            'items' => $this->adminUserService->paginatedIndex($request),
            'fields' => $this->visibleFields('index'),
            'routePrefix' => $this->routePrefix(),
            'canCreate' => $this->canCreate(),
            'canEdit' => $this->canEdit(),
            'canDelete' => $this->canDelete(),
            'canImportExport' => $this->canImportExport(),
            'headerActions' => $this->headerActions(),
        ]);
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $this->adminUserService->applyIndexFilters($query, $request);
    }

    protected function modelClass(): string
    {
        return User::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.users';
    }

    protected function routePrefix(): string
    {
        return 'admin.users';
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'phone'];
    }

    protected function fields(): array
    {
        return $this->adminUserService->formFields();
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        return $this->adminUserService->mutateFormData($data);
    }

    protected function afterSave(Model $record, Request $request): void
    {
        if ($record instanceof User) {
            $this->adminUserService->syncRoles($record, (array) $request->input('roles', []));
        }
    }

    protected function rules(?int $id = null): array
    {
        return $this->adminUserService->validationRules($id);
    }

    public function edit(int $id): View
    {
        $user = $this->adminUserService->findForAdminView($id);
        
        return view('admin.users.edit', [
            'title' => 'Chỉnh sửa thành viên: ' . $user->name,
            'user' => $user,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validate($this->rules($id));
        $this->adminUserService->updateFromAdmin($user, $data);

        return redirect()->route('admin.users.edit', $user->id)->with('status', 'Đã cập nhật thông tin thành viên thành công.');
    }

    public function show(int $id): View
    {
        $user = $this->adminUserService->findForAdminView($id);

        return view('admin.users.show', [
            'title' => 'Chi tiết thành viên: ' . $user->name,
            'user' => $user,
            'routePrefix' => $this->routePrefix(),
        ]);
    }
}
