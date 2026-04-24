<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('checkout.enable_guest_checkout', true);
        $this->migrator->add('checkout.tax_calculation_address', 'shipping');
        $this->migrator->add('checkout.prices_include_tax', false);
        $this->migrator->add('checkout.enabled_payment_gateways', ['cod', 'bank_transfer']);
        $this->migrator->add('checkout.stripe_public_key', null);
        $this->migrator->add('checkout.stripe_secret_key', null);
        $this->migrator->add('checkout.stripe_webhook_secret', null);
        $this->migrator->add('checkout.paypal_client_id', null);
        $this->migrator->add('checkout.paypal_secret', null);
        $this->migrator->add('checkout.paypal_mode', 'sandbox');
        $this->migrator->add('checkout.vnpay_tmn_code', null);
        $this->migrator->add('checkout.vnpay_hash_secret', null);
        $this->migrator->add('checkout.momo_partner_code', null);
        $this->migrator->add('checkout.momo_access_key', null);
        $this->migrator->add('checkout.momo_secret_key', null);
    }
};
