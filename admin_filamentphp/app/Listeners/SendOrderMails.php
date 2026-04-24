<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderAdminMail;
use App\Mail\OrderCustomerMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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

        // Send mail to customer
        if ($order->email) {
            Mail::to($order->email)->send(new OrderCustomerMail($order));
        }

        // Send notification to admin
        Mail::to(config('mail.from.address'))
            ->send(new OrderAdminMail($order));
    }
}
