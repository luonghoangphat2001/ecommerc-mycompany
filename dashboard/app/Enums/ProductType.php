<?php

namespace App\Enums;

enum ProductType: string
{
    case Simple = 'simple';
    case Variable = 'variable';
    case Combo = 'combo';
    case Deliverable = 'deliverable';
    case Downloadable = 'downloadable';
    case Service = 'service';

    /**
     * Default label (English).
     */
    public function label(): string
    {
        return match ($this) {
            self::Simple => 'Simple Product',
            self::Variable => 'Variable Product',
            self::Combo => 'Combo / Bundle Product',
            self::Deliverable => 'Deliverable (Simple)',
            self::Downloadable => 'Downloadable',
            self::Service => 'Service',
        };
    }

    /**
     * Vietnamese label.
     */
    public function labelVi(): string
    {
        return match ($this) {
            self::Simple => 'Sản phẩm đơn giản',
            self::Variable => 'Sản phẩm có biến thể',
            self::Combo => 'Sản phẩm combo / gói',
            self::Deliverable => 'Sản phẩm vật lý (Đơn giản)',
            self::Downloadable => 'Sản phẩm số',
            self::Service => 'Dịch vụ',
        };
    }

    public function isSimple(): bool
    {
        return in_array($this, [self::Simple, self::Deliverable]);
    }

    public function isVariable(): bool
    {
        return $this === self::Variable;
    }

    public function isCombo(): bool
    {
        return $this === self::Combo;
    }

    /**
     * Primary choices for product creation/editing.
     *
     * @return array<string, string>
     */
    public static function primaryOptions(): array
    {
        return [
            self::Simple->value => self::Simple->label(),
            self::Variable->value => self::Variable->label(),
            self::Combo->value => self::Combo->label(),
        ];
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
