<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\DepartmentAuditLog;
use App\Models\Department;
use App\Models\DepartmentAgent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DepartmentAuditLogController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return DepartmentAuditLog::class;
    }

    protected function title(): string
    {
        return 'department.audit.title';
    }

    protected function routePrefix(): string
    {
        return 'admin.department-audit-logs';
    }

    protected function searchable(): array
    {
        return ['event_id', 'action', 'status'];
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(): bool
    {
        return false;
    }

    protected function canDelete(): bool
    {
        return false;
    }

    protected function canImportExport(): bool
    {
        return false;
    }

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query->with(['department', 'agent']);
    }

    protected function fields(): array
    {
        return [
            'created_at' => ['label' => __('department.table.time')],
            'department.name' => ['label' => __('department.table.department_agent')],
            'agent.agent_code' => ['label' => __('department.table.agent_code')],
            'event_id' => ['label' => __('department.table.event_id')],
            'action' => ['label' => __('department.table.action')],
            'status' => ['label' => __('department.table.status')],
            'payload' => ['label' => __('department.table.payload'), 'type' => 'textarea', 'hide_index' => true],
        ];
    }

}
