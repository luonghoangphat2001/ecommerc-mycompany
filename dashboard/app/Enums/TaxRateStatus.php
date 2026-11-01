<?php

namespace App\Enums;

enum TaxRateStatus: string
{
    case Applied = 'applied';
    case ProductNotFound = 'product_not_found';
    case TaxDisabled = 'tax_disabled';
    case NoTax = 'no_tax';
    case NonTaxable = 'non_taxable';
    case AllocatedComboTax = 'allocated_combo_tax';
    case ComboTax = 'combo_tax';

    /**
     * Get default display label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Applied => 'Tax Applied',
            self::ProductNotFound => 'Product Not Found',
            self::TaxDisabled => 'Tax Disabled',
            self::NoTax => 'No tax',
            self::NonTaxable => 'Non-taxable',
            self::AllocatedComboTax => 'Allocated Combo Tax',
            self::ComboTax => 'Combo Tax',
        };
    }

    /**
     * Get Vietnamese display label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Applied => 'Đã áp dụng thuế',
            self::ProductNotFound => 'Không tìm thấy sản phẩm',
            self::TaxDisabled => 'Thuế đang tắt',
            self::NoTax => 'Không tính thuế',
            self::NonTaxable => 'Miễn thuế',
            self::AllocatedComboTax => 'Phân bổ thuế combo',
            self::ComboTax => 'Thuế combo',
        };
    }

    /**
     * List all options.
     *
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
     * List all options in Vietnamese.
     *
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
}
