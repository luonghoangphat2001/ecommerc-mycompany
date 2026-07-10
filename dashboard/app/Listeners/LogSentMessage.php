<?php

namespace App\Listeners;

use App\Models\MailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class LogSentMessage
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $sentMessage = $event->sent;
            $message = $sentMessage->getOriginalMessage();

            if (!$message instanceof \Symfony\Component\Mime\Email) {
                return;
            }
            
            MailLog::create([
                'from' => $this->formatAddress($message->getFrom()),
                'to' => $this->formatAddress($message->getTo()),
                'cc' => $this->formatAddress($message->getCc()),
                'bcc' => $this->formatAddress($message->getBcc()),
                'subject' => $message->getSubject(),
                'body' => $message->getHtmlBody() ?: $message->getTextBody(),
                'headers' => $message->getHeaders()->toString(),
                'message_id' => $sentMessage->getMessageId(),
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \App\Services\Logging\ModuleLogger::system()->error('log_email_failed', 'Failed to log email: ' . $e->getMessage(), ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Format email addresses to string.
     */
    protected function formatAddress(array $addresses): ?string
    {
        if (empty($addresses)) {
            return null;
        }

        return collect($addresses)
            ->map(fn($address) => $address->getName() 
                ? "{$address->getName()} <{$address->getAddress()}>" 
                : $address->getAddress())
            ->implode(', ');
    }
}
