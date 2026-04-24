<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CheckoutSettings extends Settings
{
    public bool $enable_guest_checkout;
    public string $tax_calculation_address; // 'shipping', 'billing', 'base'
    public bool $prices_include_tax;
    public array $enabled_payment_gateways;

    // Stripe
    public ?string $stripe_public_key = null;
    public ?string $stripe_secret_key = null;
    public ?string $stripe_webhook_secret = null;

    // PayPal
    public ?string $paypal_client_id = null;
    public ?string $paypal_secret = null;
    public string $paypal_mode = 'sandbox'; // sandbox or live

    // VNPay
    public ?string $vnpay_tmn_code = null;
    public ?string $vnpay_hash_secret = null;

    // Momo
    public ?string $momo_partner_code = null;
    public ?string $momo_access_key = null;
    public ?string $momo_secret_key = null;

    public static function group(): string
    {
        return 'checkout';
    }
}
