<?php

namespace App\Listeners;

use App\Ecommerce\Order\Events\OrderCreated;
use App\Mail\OrderAdminMail;
use App\Mail\OrderCustomerMail;
use App\Models\Order;
use App\Services\Logging\ModuleLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderMails implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';

    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [60, 300, 900];

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (! filled(config('mail.from.address'))) {
            ModuleLogger::order()->warning(
                'order_email_skipped',
                'Order email was skipped because mail.from.address is not configured.',
                ['order_id' => $order->id]
            );

            return;
        }

        $customerEmail = $order->shippingAddress?->email;
        if ($customerEmail) {
            $this->sendOrderMail($order, $customerEmail, OrderCustomerMail::class);
        }

        $adminEmail = config('mail.from.address') ?: config('mail.admin.address', 'admin@example.com');
        if ($adminEmail) {
            $this->sendOrderMail($order, $adminEmail, OrderAdminMail::class);
        }
    }

    private function sendOrderMail(Order $order, string $recipient, string $mailClass): void
    {
        Mail::to($recipient)->send(new $mailClass($order));
    }
}
