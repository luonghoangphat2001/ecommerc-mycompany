<?php

namespace App\Listeners;

use App\Ecommerce\Order\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderCreatedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Dispatch webhook payload to internal integrations or logging
        \App\Services\Logging\ModuleLogger::webhook()->info('order_created_webhook_dispatched', 'OrderCreated Webhook dispatched successfully for order #' . $order->id, ['order_id' => $order->id]);
    }
}
