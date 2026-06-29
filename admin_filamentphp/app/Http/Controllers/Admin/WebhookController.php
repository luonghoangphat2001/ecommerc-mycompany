<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\Webhook;

class WebhookController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return Webhook::class;
    }

    protected function title(): string
    {
        return 'Webhooks';
    }

    protected function routePrefix(): string
    {
        return 'admin.webhooks';
    }

    protected function searchable(): array
    {
        return ['name', 'url', 'secret'];
    }

    protected function fields(): array
    {
        return [
            'name' => ['label' => 'Name', 'rules' => ['required', 'string', 'max:255']],
            'url' => ['label' => 'URL', 'rules' => ['required', 'url']],
            'secret' => ['label' => 'Secret', 'rules' => ['required', 'string', 'max:255']],
            'is_active' => ['label' => 'Is Active', 'type' => 'select', 'rules' => ['required', 'boolean'], 'options' => ['1' => 'Yes', '0' => 'No']],
            'events' => ['label' => 'Events JSON', 'type' => 'textarea', 'rules' => ['required', 'string']],
        ];
    }

    protected function mutateData(array $data, ?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        if (isset($data['events']) && is_string($data['events'])) {
            $decoded = json_decode($data['events'], true);
            $data['events'] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        return $data;
    }

    protected function formData(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        if (! $record) {
            return ['events' => '[]'];
        }

        return [
            'events' => json_encode($record->events ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];
    }
}
