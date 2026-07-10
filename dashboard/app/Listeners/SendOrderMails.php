<?php

namespace App\Listeners;

use App\Ecommerce\Order\Events\OrderCreated;
use App\Mail\OrderAdminMail;
use App\Mail\OrderCustomerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderMails implements ShouldQueue
{
    use InteractsWithQueue;

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

        try {
            // Send mail to customer
            $customerEmail = $order->shippingAddress?->email;
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new OrderCustomerMail($order));
            }

            // Send notification to admin
            $adminEmail = config('mail.from.address') ?: config('mail.admin.address', 'admin@example.com');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new OrderAdminMail($order));
            }
        } catch (\Exception $e) {
            // Log error but don't fail the order creation
            \App\Services\Logging\ModuleLogger::order()->error('send_email_failed', 'Failed to send order email: ' . $e->getMessage(), ['order_id' => $order->id]);
        }
    }
}
