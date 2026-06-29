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
        return 'Webhook Logs';
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

    protected function indexQuery(Builder $query, Request $request): Builder
    {
        return $query->with('webhook');
    }

    protected function fields(): array
    {
        return [
            'webhook_id' => ['label' => 'Webhook', 'type' => 'select', 'rules' => ['required', 'integer'], 'options' => Webhook::orderBy('id')->pluck('name', 'id')->toArray()],
            'event' => ['label' => 'Event', 'rules' => ['required', 'string', 'max:100']],
            'status' => ['label' => 'Status', 'rules' => ['required', 'in:pending,processing,delivered,failed']],
            'duration' => ['label' => 'Duration (ms)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0']],
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
