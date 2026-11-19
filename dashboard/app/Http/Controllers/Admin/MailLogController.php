<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\Traits\HasReadOnlyCrudActions;
use App\Models\MailLog;

class MailLogController extends BaseCrudController
{
    use HasReadOnlyCrudActions;

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
}
