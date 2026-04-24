<?php

namespace App\Filament\Imports;

use App\Filament\Imports\BaseImporter;
use App\Contracts\Services\BrandServiceInterface;
use App\Models\Brand;
use Filament\Actions\Imports\ImportColumn;

class BrandImporter extends BaseImporter
{
    protected static ?string $model = Brand::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('name', config('app.locale'), $value))
                ->example('Nike'),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('nike'),
            ImportColumn::make('website')
                ->rules(['nullable', 'url', 'max:255'])
                ->example('https://nike.com'),
            ImportColumn::make('description')
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('description', config('app.locale'), $value ?? ''))
                ->example('Global sports brand.'),
            ImportColumn::make('is_visible')
                ->label(trans('admin.fields.visibility'))
                ->boolean()
                ->rules(['boolean'])
                ->example('yes'),
        ];
    }

    public function resolveRecord(): ?Brand
    {
        return app(BrandServiceInterface::class)->firstOrNew([
            'slug' => $this->data['slug'],
        ]);
    }
}
