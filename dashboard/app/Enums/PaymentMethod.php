<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case Cash = 'cash';
    case CreditCard = 'credit_card';
    case Stripe = 'stripe';
    case Paypal = 'paypal';
    case VnPay = 'vnpay';
    case MoMo = 'momo';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cod => __('storefront.payments.cod') ?? 'Cash on Delivery',
            self::Cash => 'Tiền mặt',
            self::CreditCard => 'Thẻ tín dụng',
            self::Stripe => __('storefront.payments.stripe') ?? 'Stripe',
            self::Paypal => __('storefront.payments.paypal') ?? 'PayPal',
            self::VnPay => 'VNPay',
            self::MoMo => 'Ví MoMo',
            self::BankTransfer => __('storefront.payments.bank_transfer') ?? 'Bank Transfer',
        };
    }
}
