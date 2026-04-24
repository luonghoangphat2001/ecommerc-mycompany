<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Actions;

class ViewPost extends ViewRecord
{
    use ViewRecord\Concerns\Translatable;

    protected static string $resource = PostResource::class;

    public function getTitle(): string | Htmlable
    {
        /** @var Post */
        $record = $this->getRecord();

        return trans('admin.blog.view');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin.blog.view');
    }

    protected function getActions(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
