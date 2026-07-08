<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\MailLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MailLogController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return MailLog::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.mail_logs';
    }

    protected function routePrefix(): string
    {
        return 'admin.mail-logs';
    }

    protected function searchable(): array
    {
        return ['to', 'subject', 'status'];
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

    protected function fields(): array
    {
        return [
            'to' => ['label' => 'To', 'rules' => ['nullable', 'string', 'max:255']],
            'subject' => ['label' => 'Subject', 'rules' => ['nullable', 'string', 'max:255']],
            'status' => ['label' => 'Status', 'rules' => ['nullable', 'string', 'max:50']],
            'opened' => ['label' => 'Opened (0/1)', 'type' => 'number', 'rules' => ['nullable', 'boolean']],
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
