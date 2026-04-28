<?php

namespace App\Filament\Imports;

use App\Filament\Imports\BaseImporter;
use App\Ecommerce\Product\Contracts\ProductServiceInterface;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;

class ProductImporter extends BaseImporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('name', config('app.locale'), $value))
                ->example('Wireless Headphones'),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('wireless-headphones'),
            ImportColumn::make('sku')
                ->rules(['nullable', 'max:255'])
                ->example('SKU-001'),
            ImportColumn::make('price')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('299000'),
            ImportColumn::make('old_price')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('399000'),
            ImportColumn::make('cost')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->example('150000'),
            ImportColumn::make('qty')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:0'])
                ->example('100'),
            ImportColumn::make('type')
                ->rules(['nullable', 'max:50'])
                ->example('product'),
            ImportColumn::make('featured')
                ->boolean()
                ->rules(['boolean'])
                ->example('no'),
            ImportColumn::make('is_visible')
                ->label(trans('admin.fields.visibility'))
                ->boolean()
                ->rules(['boolean'])
                ->example('yes'),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date'])
                ->example('2024-01-15'),
            ImportColumn::make('seo_title')
                ->label(trans('admin.fields.seo_title'))
                ->rules(['max:60'])
                ->example('Wireless Headphones - Shop'),
            ImportColumn::make('seo_description')
                ->label(trans('admin.fields.seo_description'))
                ->rules(['max:160'])
                ->example('Premium wireless headphones with noise cancellation.'),
            ImportColumn::make('brand')
                ->relationship(resolveUsing: ['slug']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        return app(ProductServiceInterface::class)->firstOrNew([
            'slug' => $this->data['slug'],
        ]);
    }
}
