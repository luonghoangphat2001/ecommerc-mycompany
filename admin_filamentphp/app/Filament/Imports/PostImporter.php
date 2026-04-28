<?php

namespace App\Filament\Imports;

use App\Filament\Imports\BaseImporter;
use App\Ecommerce\Post\Contracts\PostServiceInterface;
use App\Models\Post;
use Filament\Actions\Imports\ImportColumn;

class PostImporter extends BaseImporter
{
    protected static ?string $model = Post::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('title', config('app.locale'), $value))
                ->example('My First Post'),
            ImportColumn::make('slug')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('my-first-post'),
            ImportColumn::make('content')
                ->fillRecordUsing(fn ($record, $value) => $record->setTranslation('content', config('app.locale'), $value ?? ''))
                ->example('Post content here.'),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date'])
                ->example('2024-01-15'),
            ImportColumn::make('is_visible')
                ->label(trans('admin.fields.visibility'))
                ->boolean()
                ->rules(['boolean'])
                ->example('yes'),
            ImportColumn::make('seo_title')
                ->label(trans('admin.fields.seo_title'))
                ->rules(['max:60'])
                ->example('My First Post - Blog'),
            ImportColumn::make('seo_description')
                ->label(trans('admin.fields.seo_description'))
                ->rules(['max:160'])
                ->example('A short description for search engines.'),
        ];
    }

    public function resolveRecord(): ?Post
    {
        return app(PostServiceInterface::class)->firstOrNew([
            'slug' => $this->data['slug'],
        ]);
    }
}
