<?php

namespace App\Traits;

use App\Jobs\DispatchWebhookJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasWebhooks
{
    public static function bootHasWebhooks(): void
    {
        static::created(function (Model $model) {
            $model->triggerWebhook('created');
        });

        static::updated(function (Model $model) {
            $model->triggerWebhook('updated');
        });

        static::deleted(function (Model $model) {
            $action = 'deleted';
            // If using soft deletes and it was a soft delete, use 'deleted'
            // If it was a force delete, it might still be 'deleted' or 'forceDeleted'
            // For simplicity in plan, we use 'deleted'
            $model->triggerWebhook($action);
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restored(function (Model $model) {
                $model->triggerWebhook('restored');
            });
        }
    }

    public function triggerWebhook(string $action): void
    {
        $topic = $this->getWebhookTopic();
        $event = "{$topic}.{$action}";
        
        // We pass the model and changes. 
        // The job will handle the Resource transformation to ensure background performance.
        DispatchWebhookJob::dispatch(
            $event, 
            get_class($this), 
            $this->id, 
            $action === 'updated' ? $this->getChanges() : []
        );
    }

    public function getWebhookTopic(): string
    {
        if (property_exists($this, 'webhookTopic')) {
            return $this->webhookTopic;
        }

        return strtolower(class_basename($this));
    }
}
