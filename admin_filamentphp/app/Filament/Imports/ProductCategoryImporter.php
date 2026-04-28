<?php

namespace App\Filament\Imports;

use App\Filament\Imports\BaseImporter;
use App\Ecommerce\Product\Contracts\ProductCategoryServiceInterface;
use App\Models\ProductCategory;
use Filament\Actions\Imports\ImportColumn;

class ProductCategoryImporter extends BaseImporter
{
    protected static ?string $model = ProductCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('name', config('app.locale'), $value))
                ->example('Electronics'),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('electronics'),
            ImportColumn::make('description')
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('description', config('app.locale'), $value ?? ''))
                ->example('All electronic products.'),
            ImportColumn::make('sort')
                ->label(trans('admin.fields.position'))
                ->numeric()
                ->rules(['nullable', 'integer'])
                ->example('1'),
            ImportColumn::make('is_visible')
                ->label(trans('admin.fields.visibility'))
                ->boolean()
                ->rules(['boolean'])
                ->example('yes'),
            ImportColumn::make('seo_title')
                ->label(trans('admin.fields.seo_title'))
                ->rules(['max:60'])
                ->example('Electronics - Shop'),
            ImportColumn::make('seo_description')
                ->label(trans('admin.fields.seo_description'))
                ->rules(['max:160'])
                ->example('Browse our electronics collection.'),
        ];
    }

    public function resolveRecord(): ?ProductCategory
    {
        return app(ProductCategoryServiceInterface::class)->firstOrNew([
            'slug' => $this->data['slug'],
        ]);
    }
}
