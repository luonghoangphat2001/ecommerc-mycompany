<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\MailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $mailClass;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order, string $mailClass)
    {
        $this->order = $order;
        $this->mailClass = $mailClass;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->order->customer_email) {
                return;
            }

            $mailable = new $this->mailClass($this->order);
            
            Mail::to($this->order->customer_email)->send($mailable);

            // Optional: Log success to MailLog if needed, 
            // but Laravel's Mailer often handles this via events.
        } catch (Exception $e) {
            // Log error or let it fail for retry
            throw $e;
        }
    }
}
