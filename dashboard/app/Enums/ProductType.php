<?php

namespace App\Enums;

enum ProductType: string
{
    case Deliverable = 'deliverable';
    case Downloadable = 'downloadable';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::Deliverable => 'Deliverable',
            self::Downloadable => 'Downloadable',
            self::Service => 'Service',
        };
    }
}
