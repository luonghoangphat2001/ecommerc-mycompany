<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WebhookLogController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return WebhookLog::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.webhook_logs';
    }

    protected function routePrefix(): string
    {
        return 'admin.webhook-logs';
    }

    protected function searchable(): array
    {
        return ['event', 'status'];
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
        return $query->with(['webhook', 'department', 'agent']);
    }

    protected function fields(): array
    {
        return [
            'created_at' => ['label' => 'Time'],
            'webhook_id' => ['label' => 'Webhook', 'type' => 'select', 'rules' => ['nullable', 'integer'], 'options' => Webhook::orderBy('id')->pluck('name', 'id')->toArray()],
            'department.name' => ['label' => 'Department'],
            'agent.agent_code' => ['label' => 'Agent'],
            'event_id' => ['label' => 'Event ID'],
            'action' => ['label' => 'Action'],
            'event' => ['label' => 'Event Type', 'rules' => ['required', 'string', 'max:100']],
            'status' => ['label' => 'Status', 'rules' => ['required', 'string']],
            'duration' => ['label' => 'Duration (ms)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
            'payload' => ['label' => 'Payload', 'type' => 'textarea', 'hide_index' => true],
        ];
    }

    public function create(): never
    {
        throw new NotFoundHttpException();
    }

    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }

    public function edit(int $id): never
    {
        throw new NotFoundHttpException();
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort(404);
    }

    public function destroy(int $id): RedirectResponse
    {
        abort(404);
    }
}
