<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookLog;
use App\Settings\WebhookSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [10, 60, 300, 600, 1800]; // Exponential backoff

    public function __construct(
        protected string $event,
        protected string $modelClass,
        protected int $modelId,
        protected array $changes = []
    ) {}

    public function handle(): void
    {
        if (!app(WebhookSettings::class)->enabled) {
            return;
        }

        $model = $this->modelClass::find($this->modelId);
        
        if (!$model && !Str::contains($this->event, 'deleted')) {
            return;
        }

        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $this->event)
            ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        $payloadData = $this->getPayloadData($model);

        foreach ($webhooks as $webhook) {
            $this->deliver($webhook, $payloadData);
        }
    }

    protected function getPayloadData($model): array
    {
        if (!$model) {
            return ['id' => $this->modelId];
        }

        $resourceClass = $this->getResourceClass(get_class($model));
        
        if ($resourceClass) {
            return $resourceClass::make($model)->resolve();
        }

        return $model->toArray();
    }

    protected function getResourceClass(string $modelClass): ?string
    {
        $baseName = class_basename($modelClass);
        $resourceClass = "App\\Http\\Resources\\Api\\{$baseName}Resource";
        
        return class_exists($resourceClass) ? $resourceClass : null;
    }

    protected function deliver(Webhook $webhook, array $data): void
    {
        $payload = [
            'webhook_id' => (string) $webhook->id,
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
            'changes' => $this->changes,
        ];

        $jsonPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonPayload, $webhook->secret);

        $startTime = microtime(true);
        
        try {
            $response = Http::withHeaders([
                'X-Hub-Signature' => $signature,
                'Content-Type' => 'application/json',
                'User-Agent' => 'Antigravity-Webhook-Sender/1.0',
            ])->timeout(10)->post($webhook->url, $payload);

            $duration = (int) ((microtime(true) - $startTime) * 1000);
            
            $status = $response->successful() ? 'delivered' : 'failed';

            $this->logDelivery($webhook, $payload, $response->json(), $status, $duration);
        } catch (\Exception $e) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $this->logDelivery($webhook, $payload, ['error' => $e->getMessage()], 'failed', $duration);
            
            \App\Services\Logging\ModuleLogger::webhook()->error('webhook_delivery_failed', "Webhook delivery failed to {$webhook->url}: " . $e->getMessage(), ['webhook_id' => $webhook->id, 'url' => $webhook->url, 'event' => $this->event]);
            
            throw $e; // Re-throw to trigger retry
        }
    }

    protected function logDelivery(Webhook $webhook, array $payload, ?array $response, string $status, int $duration): void
    {
        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => $this->event,
            'payload' => $payload,
            'response' => $response,
            'status' => $status,
            'duration' => $duration,
            'created_at' => now(),
        ]);

        \App\Services\Logging\ModuleLogger::webhook()->info('webhook_delivered', "Webhook {$status} to {$webhook->url}", [
            'webhook_id' => $webhook->id,
            'event' => $this->event,
            'url' => $webhook->url,
            'status' => $status,
            'duration' => $duration,
            'response' => $response,
        ]);
    }
}
