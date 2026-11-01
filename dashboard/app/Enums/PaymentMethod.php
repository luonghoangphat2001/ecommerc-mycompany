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

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Cash on Delivery',
            self::Cash => 'Cash',
            self::CreditCard => 'Credit Card',
            self::Stripe => 'Stripe',
            self::Paypal => 'PayPal',
            self::VnPay => 'VNPay',
            self::MoMo => 'MoMo Wallet',
            self::BankTransfer => 'Bank Transfer',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Cod => 'Thanh toán khi nhận hàng (COD)',
            self::Cash => 'Tiền mặt',
            self::CreditCard => 'Thẻ tín dụng',
            self::Stripe => 'Stripe',
            self::Paypal => 'PayPal',
            self::VnPay => 'VNPay',
            self::MoMo => 'Ví MoMo',
            self::BankTransfer => 'Chuyển khoản ngân hàng',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function optionsVi(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->labelVi();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
