<?php

namespace App\Listeners;

use App\Ecommerce\Order\Events\OrderCreated;
use App\Jobs\DispatchWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderCreatedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [60, 300, 900];

    public string $queue = 'webhooks';

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        DispatchWebhookJob::dispatch('order.created', $order::class, (int) $order->getKey())
            ->onQueue('webhooks');
    }
}
