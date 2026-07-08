<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Models\LanguageLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class LanguageLineController extends BaseCrudController
{
    protected function modelClass(): string
    {
        return LanguageLine::class;
    }

    protected function title(): string
    {
        return 'admin.sidebar.language_lines';
    }

    protected function routePrefix(): string
    {
        return 'admin.language-lines';
    }

    protected function searchable(): array
    {
        return ['group', 'key'];
    }

    protected function headerActions(): array
    {
        return [
            [
                'label' => 'Sync từ resources/lang',
                'url' => route('admin.language-lines.sync'),
                'method' => 'post',
                'class' => 'btn-secondary',
            ],
        ];
    }

    public function syncFromFiles(): RedirectResponse
    {
        $basePath = resource_path('lang');
        $synced = 0;
        $skipped = 0;

        foreach (File::directories($basePath) as $localePath) {
            $locale = basename($localePath);

            foreach (File::files($localePath) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $group = $file->getFilenameWithoutExtension();
                $lines = require $file->getRealPath();

                if (! is_array($lines)) {
                    $skipped++;
                    continue;
                }

                foreach (Arr::dot($lines) as $key => $value) {
                    if (is_array($value)) {
                        continue;
                    }

                    $record = LanguageLine::firstOrNew(['group' => $group, 'key' => $key]);
                    $text = $record->text ?: [];
                    $text[$locale] = (string) $value;
                    $record->text = $text;
                    $record->save();
                    $synced++;
                }
            }
        }

        return redirect()
            ->route('admin.language-lines.index')
            ->with('status', "Đồng bộ lang hoàn tất: {$synced} dòng, {$skipped} file bỏ qua");
    }

    protected function fields(): array
    {
        return [
            'group' => ['label' => 'Group', 'rules' => ['required', 'string', 'max:255']],
            'key' => ['label' => 'Key', 'rules' => ['required', 'string', 'max:255']],
            'text_vi' => ['label' => 'Tiếng Việt', 'rules' => ['nullable', 'string'], 'virtual' => true],
            'text_en' => ['label' => 'English', 'rules' => ['nullable', 'string'], 'virtual' => true],
        ];
    }

    protected function mutateData(array $data, ?Model $record = null): array
    {
        $data['text'] = [
            'vi' => $data['text_vi'] ?? '',
            'en' => $data['text_en'] ?? '',
        ];

        unset($data['text_vi'], $data['text_en']);

        return $data;
    }

    protected function formData(?Model $record = null): array
    {
        if (! $record) {
            return [];
        }

        return [
            'text_vi' => $record->text['vi'] ?? '',
            'text_en' => $record->text['en'] ?? '',
        ];
    }
}
