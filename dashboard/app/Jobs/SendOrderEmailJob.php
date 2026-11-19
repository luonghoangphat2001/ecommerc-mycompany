<?php

namespace App\Jobs;

use App\Mail\OrderAdminMail;
use App\Mail\OrderCustomerMail;
use App\Models\Order;
use App\Support\Infrastructure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [60, 300, 900];

    /**
     * @var Order
     */
    protected int $orderId;

    /**
     * @var string
     */
    protected $mailClass;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId, string $mailClass)
    {
        $this->orderId = $orderId;
        $this->mailClass = $mailClass;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['shippingAddress', 'billingAddress', 'user'])->findOrFail($this->orderId);
        $allowedMailClasses = [OrderCustomerMail::class, OrderAdminMail::class];
        if (! in_array($this->mailClass, $allowedMailClasses, true)) {
            throw new \InvalidArgumentException('Unsupported order mail class.');
        }

        $recipient = $this->mailClass === OrderAdminMail::class
            ? Infrastructure::adminEmail()
            : (Infrastructure::customerEmail($order) ?? $order->customer_email);

        if (! $recipient) {
            return;
        }

        Mail::to($recipient)->send(new $this->mailClass($order));
    }
}
