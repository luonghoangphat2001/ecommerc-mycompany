<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\File;

class SystemLogViewer extends Component
{
    public $selectedFile = null;
    public $content = '';

    public function mount()
    {
        $files = $this->getLogFiles();
        if (!empty($files)) {
            $this->selectedFile = array_key_first($files);
            $this->loadLogContent($this->selectedFile);
        }
    }

    public function updatedSelectedFile($value)
    {
        $this->loadLogContent($value);
    }

    public function getLogFiles()
    {
        $path = storage_path('logs/custom');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
            return [];
        }

        return collect(File::files($path))
            ->mapWithKeys(fn ($file) => [$file->getFilename() => $file->getFilename()])
            ->sortDesc()
            ->toArray();
    }

    protected function loadLogContent(?string $file)
    {
        if (!$file) {
            $this->content = '';
            return;
        }

        $path = storage_path('logs/custom/' . $file);
        if (File::exists($path)) {
            $this->content = File::get($path);
        } else {
            $this->content = __('admin.logs.file_not_found');
        }
    }

    public function render()
    {
        return view('livewire.settings.system-log-viewer', [
            'files' => $this->getLogFiles(),
        ]);
    }
}
