<?php

namespace App\Filament\Exports;

use App\Filament\Exports\BaseExporter;
use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;

class ProductExporter extends BaseExporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ...static::commonColumns(),
            ExportColumn::make('name')
                ->getStateUsing(fn ($record) => $record->getTranslation('name', config('app.locale'))),
            ExportColumn::make('slug'),
            ExportColumn::make('sku'),
            ExportColumn::make('price'),
            ExportColumn::make('old_price'),
            ExportColumn::make('cost'),
            ExportColumn::make('qty'),
            ExportColumn::make('type'),
            ExportColumn::make('featured'),
            ExportColumn::make('is_visible'),
            ExportColumn::make('published_at'),
            ExportColumn::make('seo_title'),
            ExportColumn::make('seo_description'),
        ];
    }
}
