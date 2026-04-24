<?php

namespace App\Filament\Exports;

use App\Filament\Exports\BaseExporter;
use App\Models\ProductCategory;
use Filament\Actions\Exports\ExportColumn;

class ProductCategoryExporter extends BaseExporter
{
    protected static ?string $model = ProductCategory::class;

    public static function getColumns(): array
    {
        return [
            ...static::commonColumns(),
            ExportColumn::make('name')
                ->label(trans('admin.fields.name'))
                ->getStateUsing(fn ($record) => $record->getTranslation('name', config('app.locale'))),
            ExportColumn::make('slug')
                ->label(trans('admin.fields.slug')),
            ExportColumn::make('description')
                ->label(trans('admin.fields.description'))
                ->getStateUsing(fn ($record) => $record->getTranslation('description', config('app.locale'))),
            ExportColumn::make('sort')
                ->label(trans('admin.fields.position')),
            ExportColumn::make('is_visible')
                ->label(trans('admin.fields.visibility')),
            ExportColumn::make('seo_title')
                ->label(trans('admin.fields.seo_title')),
            ExportColumn::make('seo_description')
                ->label(trans('admin.fields.seo_description')),
        ];
    }
}
