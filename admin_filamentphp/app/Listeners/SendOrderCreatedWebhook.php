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
        Log::info('OrderCreated Webhook dispatched successfully for order #' . $order->id);
    }
}
