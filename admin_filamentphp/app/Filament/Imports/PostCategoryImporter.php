<?php

namespace App\Filament\Imports;

use App\Filament\Imports\BaseImporter;
use App\Ecommerce\Post\Contracts\PostCategoryServiceInterface;
use App\Models\PostCategory;
use Filament\Actions\Imports\ImportColumn;

class PostCategoryImporter extends BaseImporter
{
    protected static ?string $model = PostCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('name', config('app.locale'), $value))
                ->example('Technology'),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('technology'),
            ImportColumn::make('description')
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('description', config('app.locale'), $value ?? ''))
                ->example('All about technology.'),
            ImportColumn::make('is_visible')
                ->label(trans('admin.fields.visibility'))
                ->boolean()
                ->rules(['boolean'])
                ->example('yes'),
        ];
    }

    public function resolveRecord(): ?PostCategory
    {
        return app(PostCategoryServiceInterface::class)->firstOrNew([
            'slug' => $this->data['slug'],
        ]);
    }
}
