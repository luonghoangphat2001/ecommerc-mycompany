<?php

namespace App\Filament\Exports;

use App\Filament\Exports\BaseExporter;
use App\Models\Post;
use Filament\Actions\Exports\ExportColumn;

class PostExporter extends BaseExporter
{
    protected static ?string $model = Post::class;

    public static function getColumns(): array
    {
        return [
            ...static::commonColumns(),
            ExportColumn::make('title')
                ->label(trans('admin.fields.title'))
                ->getStateUsing(fn ($record) => $record->getTranslation('title', config('app.locale'))),
            ExportColumn::make('slug')
                ->label(trans('admin.fields.slug')),
            ExportColumn::make('content')
                ->label(trans('admin.fields.content'))
                ->getStateUsing(fn ($record) => $record->getTranslation('content', config('app.locale'))),
            ExportColumn::make('published_at')
                ->label(trans('admin.fields.published_at')),
            ExportColumn::make('is_visible')
                ->label(trans('admin.fields.visibility')),
            ExportColumn::make('seo_title')
                ->label(trans('admin.fields.seo_title')),
            ExportColumn::make('seo_description')
                ->label(trans('admin.fields.seo_description')),
        ];
    }
}
