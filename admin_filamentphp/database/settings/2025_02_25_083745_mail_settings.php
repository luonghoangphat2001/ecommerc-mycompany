<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('emails.sender_name', 'Admin');
        $this->migrator->add('emails.sender_email', 'admin@admin.com');
        $this->migrator->add('emails.base_color', '#4f46e5');
        $this->migrator->add('emails.notifications', [
            'new_order' => ['enabled' => true, 'recipients' => [], 'template' => 'emails.orders.new_default'],
            'cancelled_order' => ['enabled' => true, 'template' => 'emails.orders.cancelled_default'],
            'failed_order' => ['enabled' => true, 'template' => 'emails.orders.failed_default'],
            'order_on_hold' => ['enabled' => true, 'template' => 'emails.orders.on_hold_default'],
            'processing_order' => ['enabled' => true, 'template' => 'emails.orders.processing_default'],
            'completed_order' => ['enabled' => true, 'template' => 'emails.orders.completed_default'],
            'refunded_order' => ['enabled' => true, 'template' => 'emails.orders.refunded_default'],
            'order_details' => ['enabled' => true, 'template' => 'emails.orders.details_default'],
            'customer_note' => ['enabled' => true, 'template' => 'emails.orders.customer_note'],
            'reset_password' => ['enabled' => true, 'template' => 'emails.auth.reset_password'],
            'new_account' => ['enabled' => true, 'template' => 'emails.auth.new_account'],
            'store_credit' => ['enabled' => true, 'template' => 'emails.customer.store_credit'],
            'shipping_fulfillment' => ['enabled' => true, 'template' => 'emails.shipping.fulfillment'],
            'payment_retry_customer' => ['enabled' => true, 'template' => 'emails.payment.retry_customer'],
            'payment_retry_admin' => ['enabled' => true, 'recipients' => [], 'template' => 'emails.payment.retry_admin'],
        ]);
    }

    public function down(): void
    {
        $this->migrator->delete('emails.sender_name');
        $this->migrator->delete('emails.sender_email');
        $this->migrator->delete('emails.base_color');
        $this->migrator->delete('emails.notifications');
        $this->migrator->delete('emails.smtp_host');
        $this->migrator->delete('emails.smtp_username');
        $this->migrator->delete('emails.smtp_port');
        $this->migrator->delete('emails.smtp_encryption');
    }
};
